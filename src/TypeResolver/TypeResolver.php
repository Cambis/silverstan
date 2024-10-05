<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryProviderInterface;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use ReflectionProperty;
use function array_key_exists;
use function is_array;
use function is_string;
use function strtok;

final readonly class TypeResolver
{
    /**
     * @var array<class-string, class-string>
     */
    private const DBFIELD_TO_TYPE_MAPPING = [
        'SilverStripe\ORM\FieldType\DBBoolean' => BooleanType::class,
        'SilverStripe\ORM\FieldType\DBDecimal' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBFloat' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBInt' => IntegerType::class,
    ];

    public function __construct(
        private ConfigurationResolver $configurationResolver,
        private ReflectionProvider $reflectionProvider,
        private ReflectionResolver $reflectionResolver,
        private TypeFactory $typeFactory,
        private TypeResolverRegistryProviderInterface $typeResolverRegistryProvider
    ) {
    }

    /**
     * Resolve all injected property types using the registered resolvers.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface
     * @return Type[]
     */
    public function resolveInjectedPropertyTypes(ClassReflection $classReflection): array
    {
        $types = [];

        foreach ($classReflection->getNativeReflection()->getProperties(ReflectionProperty::IS_PRIVATE) as $reflectionProperty) {
            $property = $this->reflectionResolver->resolveConfigurationPropertyReflection($classReflection, $reflectionProperty->getName());

            if (!$property instanceof PropertyReflection) {
                continue;
            }

            $types = [...$types, ...$this->resolveInjectedPropertyTypesFromConfigurationProperty($classReflection, $reflectionProperty->getName())];
        }

        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return $types;
        }

        return [
            ...$types,
            ...$this->resolveInjectedPropertyTypes($classReflection->getParentClass()),
        ];
    }

    /**
     * Resolve injected property types using the registered resolvers.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface
     * @return Type[]
     */
    public function resolveInjectedPropertyTypesFromConfigurationProperty(ClassReflection $classReflection, string $propertyName): array
    {
        foreach ($this->typeResolverRegistryProvider->getRegistry()->getPropertyTypeResolvers() as $typeResolver) {
            if ($typeResolver->getConfigurationPropertyName() !== $propertyName) {
                continue;
            }

            return $typeResolver->resolve($classReflection);
        }

        return [];
    }

    /**
     * Resolve injected method types using the registered resolvers.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface
     * @return Type[]
     */
    public function resolveInjectedMethodTypesFromConfigurationProperty(ClassReflection $classReflection, string $propertyName): array
    {
        foreach ($this->typeResolverRegistryProvider->getRegistry()->getMethodTypeResolvers() as $typeResolver) {
            if ($typeResolver->getConfigurationPropertyName() !== $propertyName) {
                continue;
            }

            return $typeResolver->resolve($classReflection);
        }

        return [];
    }

    /**
     * Resolve all injected method types using the registered resolvers.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface
     * @return Type[]
     */
    public function resolveInjectedMethodTypes(ClassReflection $classReflection): array
    {
        $types = [];

        foreach ($classReflection->getNativeReflection()->getProperties(ReflectionProperty::IS_PRIVATE) as $reflectionProperty) {
            $property = $this->reflectionResolver->resolveConfigurationPropertyReflection($classReflection, $reflectionProperty->getName());

            if (!$property instanceof PropertyReflection) {
                continue;
            }

            $types = [...$types, ...$this->resolveInjectedMethodTypesFromConfigurationProperty($classReflection, $reflectionProperty->getName())];
        }

        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return $types;
        }

        return [
            ...$types,
            ...$this->resolveInjectedMethodTypes($classReflection->getParentClass()),
        ];
    }

    public function resolveConfigurationPropertyType(?ClassReflection $classReflection, string $propertyName): ?Type
    {
        $property = $this->reflectionResolver->resolveConfigurationPropertyReflection($classReflection, $propertyName);

        if (!$property instanceof PropertyReflection) {
            return null;
        }

        return $property->getReadableType();
    }

    /**
     * @param class-string $className
     * @param class-string $fieldType
     */
    public function resolveDBFieldType(string $className, string $fieldName, string $fieldType): Type
    {
        $objectClassName = $this->configurationResolver->resolveClassName($className);
        $objectClassReflection = $this->reflectionProvider->getClass($objectClassName);

        // If there is an existing property tag, return that first
        foreach ($objectClassReflection->getPropertyTags() as $propertyTagName => $propertyTag) {
            if ($propertyTagName !== $fieldName) {
                continue;
            }

            $propertyType = $propertyTag->getReadableType();

            if (!$propertyType instanceof Type) {
                continue;
            }

            return $propertyType;
        }

        $field = $this->configurationResolver->resolveClassName(strtok($fieldType, '('));
        $fieldClassReflection = $this->reflectionProvider->getClass($field);

        foreach (self::DBFIELD_TO_TYPE_MAPPING as $dbClass => $type) {
            if (!$this->reflectionProvider->hasClass($dbClass)) {
                continue;
            }

            if (!$fieldClassReflection->is($dbClass)) {
                continue;
            }

            return new $type();
        }

        return TypeCombinator::addNull(new StringType());
    }

    /**
     * Resolve the type of a relation field from `has_many`, `many_many` etc.
     *
     * @param string[]|string $fieldType
     */
    public function resolveRelationFieldType(array|string $fieldType): Type
    {
        $className = '';

        if (is_array($fieldType) && !array_key_exists('through', $fieldType)) {
            return new MixedType();
        }

        if (is_array($fieldType)) {
            $className = $fieldType['through'];
        }

        if (is_string($fieldType)) {
            $className = $this->configurationResolver->resolveDotNotation($fieldType);
        }

        if ($this->reflectionProvider->hasClass($className)) {
            $className = $this->configurationResolver->resolveClassName($className);
        }

        return $this->typeFactory->createExtensibleTypeFromType(new ObjectType($className));
    }
}
