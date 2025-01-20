<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\LazyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryProviderInterface;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function array_key_exists;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;
use function str_contains;
use function str_starts_with;
use function strtok;
use function substr;
use function trim;

final class TypeResolver
{
    /**
     * @readonly
     */
    private ConfigurationResolver $configurationResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private TypeResolverRegistryProviderInterface $typeResolverRegistryProvider;
    /**
     * @var array<class-string, class-string>
     */
    private const DBFIELD_TO_TYPE_MAPPING = [
        'SilverStripe\ORM\FieldType\DBBoolean' => BooleanType::class,
        'SilverStripe\ORM\FieldType\DBDecimal' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBFloat' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBInt' => IntegerType::class,
    ];

    public function __construct(ConfigurationResolver $configurationResolver, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, TypeFactory $typeFactory, TypeResolverRegistryProviderInterface $typeResolverRegistryProvider)
    {
        $this->configurationResolver = $configurationResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->typeFactory = $typeFactory;
        $this->typeResolverRegistryProvider = $typeResolverRegistryProvider;
    }

    /**
     * Resolve all injected property types using the registered resolvers.
     * Does not resolve implementors of `Cambis\Silverstan\TypeResolver\Contract\LazyTypeResolverInterface`.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface
     * @return Type[]
     */
    public function resolveInjectedPropertyTypes(ClassReflection $classReflection): array
    {
        $types = [];

        foreach ($this->typeResolverRegistryProvider->getRegistry()->getPropertyTypeResolvers() as $typeResolver) {
            if ($typeResolver instanceof LazyTypeResolverInterface) {
                continue;
            }

            $types = array_merge($types, $typeResolver->resolve($classReflection));
        }

        return $types;
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
     * Does not resolve implementors of `Cambis\Silverstan\TypeResolver\Contract\LazyTypeResolverInterface`.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface
     * @return Type[]
     */
    public function resolveInjectedMethodTypes(ClassReflection $classReflection): array
    {
        $types = [];

        foreach ($this->typeResolverRegistryProvider->getRegistry()->getMethodTypeResolvers() as $typeResolver) {
            if ($typeResolver instanceof LazyTypeResolverInterface) {
                continue;
            }

            $types = array_merge($types, $typeResolver->resolve($classReflection));
        }

        return $types;
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
     * Resolve the type of a field from `SilverStripe\ORM\DataObject::$db`.
     *
     * @param class-string $fieldType
     */
    public function resolveDBFieldType(ClassReflection $classReflection, string $fieldName, string $fieldType): Type
    {
        // Check for custom get<fieldName>() function https://docs.silverstripe.org/en/5/developer_guides/model/data_types_and_casting/#overriding
        if ($classReflection->hasNativeMethod('get' . $fieldName)) {
            return $classReflection->getNativeMethod('get' . $fieldName)->getVariants()[0]->getReturnType();
        }

        $field = $this->configurationResolver->resolveClassName(trim(strtok($fieldType, '(')));

        // If we can't resolve the class of the field it is likely that there is an error in the $db configuration, return an error type
        if (!$this->reflectionProvider->hasClass($field)) {
            return new ErrorType();
        }

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
     * Resolve the type of a relation field from `SilverStripe\ORM\DataObject::$has_many`, `SilverStripe\ORM\DataObject::$many_many` etc.
     *
     * @param string[]|string $fieldType
     */
    public function resolveRelationFieldType($fieldType): Type
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

    /**
     * @param array<mixed>|bool|int|string $fieldType
     */
    public function resolveDependencyFieldType($fieldType): Type
    {
        if (is_array($fieldType)) {
            return new ArrayType(new IntegerType(), new MixedType());
        }

        if (is_bool($fieldType)) {
            return new BooleanType();
        }

        if (is_numeric($fieldType)) {
            return new IntegerType();
        }

        $name = $fieldType;

        // Remove the prefix
        if (strpos($name, '%$') !== false) {
            $name = $this->configurationResolver->resolvePrefixNotation($fieldType);
        }

        // Remove leading backslash
        if (strncmp($name, '\\', strlen('\\')) === 0) {
            $name = substr($name, 1);
        }

        if (strpos($name, '.') !== false) {
            $name = $this->configurationResolver->resolveDotNotation($fieldType);
        }

        if (!$this->reflectionProvider->hasClass($name)) {
            return new StringType();
        }

        if ($this->reflectionProvider->hasClass($name)) {
            $name = $this->configurationResolver->resolveClassName($name);
        }

        return new ObjectType($name);
    }
}
