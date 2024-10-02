<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\InjectionResolver\InjectionResolver;
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
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use function array_key_exists;
use function is_array;
use function is_string;

final readonly class TypeResolver
{
    /**
     * @var array<class-string<DBField>, class-string<Type>>
     */
    private const DBFIELD_TO_TYPE_MAPPING = [
        'SilverStripe\ORM\FieldType\DBBoolean' => BooleanType::class,
        'SilverStripe\ORM\FieldType\DBDecimal' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBFloat' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBInt' => IntegerType::class,
    ];

    public function __construct(
        private ConfigurationResolver $configurationResolver,
        private InjectionResolver $injectionResolver,
        private ReflectionProvider $reflectionProvider,
        private ReflectionResolver $reflectionResolver,
        private TypeFactory $typeFactory,
        private TypeResolverRegistryProviderInterface $typeResolverRegistryProvider
    ) {
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
     * @param class-string<DBField> $fieldType
     */
    public function resolveDBFieldType(string $className, string $fieldName, string $fieldType): Type
    {
        /** @var DBField $field */
        $field = $this->injectionResolver->create($fieldType, 'Temp');
        $classReflection = $this->reflectionProvider->getClass($field::class);

        foreach (self::DBFIELD_TO_TYPE_MAPPING as $dbClass => $type) {
            if (!$this->reflectionProvider->hasClass($dbClass)) {
                continue;
            }

            if (!$classReflection->is($dbClass)) {
                continue;
            }

            return new $type();
        }

        // Instantiate the object so we can check for required fields
        $object = $this->injectionResolver->create($className);

        // Fallback case
        if (!$object instanceof DataObject && !$object instanceof Extension) {
            return new StringType();
        }

        // If the object is an extension, create a mock DataObject and add the extension to it
        if ($object instanceof Extension) {
            $object = new class extends DataObject implements TestOnly {};
            $object::add_extension($className);
        }

        // Check if the field is required
        if ($object->getCMSCompositeValidator()->fieldIsRequired($fieldName)) {
            return new StringType();
        }

        // This is not required and therefore is nullable
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
            $className = $this->injectionResolver->resolveInjectedClassName($className);
        }

        return $this->typeFactory->createExtensibleTypeFromType(new ObjectType($className));
    }
}
