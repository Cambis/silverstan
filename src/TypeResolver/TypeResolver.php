<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Normaliser\Normaliser;
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
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use function array_key_exists;
use function is_array;
use function is_bool;
use function is_numeric;

/**
 * @api
 */
final readonly class TypeResolver
{
    public function __construct(
        private ConfigurationResolver $configurationResolver,
        private Normaliser $normaliser,
        private ReflectionProvider $reflectionProvider,
        private ReflectionResolver $reflectionResolver,
        private TypeFactory $typeFactory,
        private TypeResolverRegistryProviderInterface $typeResolverRegistryProvider
    ) {
    }

    /**
     * Resolve all injected property types using the registered resolvers.
     * Does not resolve implementors of `Cambis\Silverstan\TypeResolver\Contract\LazyTypeResolverInterface`.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface
     * @return array<non-empty-string, Type>
     */
    public function resolveInjectedPropertyTypes(ClassReflection $classReflection): array
    {
        $types = [];

        foreach ($this->typeResolverRegistryProvider->getRegistry()->getPropertyTypeResolvers() as $typeResolver) {
            if ($typeResolver instanceof LazyTypeResolverInterface) {
                continue;
            }

            $types = [...$types, ...$typeResolver->resolve($classReflection)];
        }

        return $types;
    }

    /**
     * Resolve injected property types using the registered resolvers.
     *
     * @see \Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface
     * @return array<non-empty-string, Type>
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
     * @return array<non-empty-string, Type>
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
     * @return array<non-empty-string, Type>
     */
    public function resolveInjectedMethodTypes(ClassReflection $classReflection): array
    {
        $types = [];

        foreach ($this->typeResolverRegistryProvider->getRegistry()->getMethodTypeResolvers() as $typeResolver) {
            if ($typeResolver instanceof LazyTypeResolverInterface) {
                continue;
            }

            $types = [...$types, ...$typeResolver->resolve($classReflection)];
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
    public function resolveDBFieldType(string $fieldType): Type
    {
        $field = $this->configurationResolver->resolveClassName($this->normaliser->normaliseBracketNotation($fieldType));

        // If we can't resolve the class of the field it is likely that there is an error in the $db configuration, return an error type
        if (!$this->reflectionProvider->hasClass($field)) {
            return new ErrorType();
        }

        // Return the field as an object type
        return new ObjectType($field);
    }

    /**
     * Resolve the type of a relation field from `SilverStripe\ORM\DataObject::$has_many`, `SilverStripe\ORM\DataObject::$many_many` etc.
     *
     * @param string[]|string $fieldType
     */
    public function resolveRelationFieldType(array|string $fieldType): Type
    {
        if (is_array($fieldType)) {
            return $this->resolveArrayRelationFieldType($fieldType);
        }

        return $this->resolveStringRelationFieldType($fieldType);
    }

    /**
     * @param array<mixed>|bool|int|string $fieldType
     */
    public function resolveDependencyFieldType(array|bool|int|string $fieldType): Type
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

        // Remove the prefix
        $name = $this->normaliser->normalisePrefixNotation($fieldType);

        // Remove leading backslash
        $name = $this->normaliser->normaliseNamespace($name);

        // Remove dot notation
        $name = $this->normaliser->normaliseDotNotation($name);

        if (!$this->reflectionProvider->hasClass($name)) {
            return new StringType();
        }

        if ($this->reflectionProvider->hasClass($name)) {
            $name = $this->configurationResolver->resolveClassName($name);
        }

        return new ObjectType($name);
    }

    /**
     * @param string[] $fieldType
     */
    private function resolveArrayRelationFieldType(array $fieldType): Type
    {
        if (array_key_exists('class', $fieldType)) {
            return $this->resolveStringRelationFieldType($fieldType['class']);
        }

        if (array_key_exists('through', $fieldType)) {
            return $this->resolveStringRelationFieldType($fieldType['through']);
        }

        // Likely an error in the configuration, return an error type
        return new ErrorType();
    }

    private function resolveStringRelationFieldType(string $fieldType): Type
    {
        $className = $this->normaliser->normaliseDotNotation($fieldType);

        if (!$this->reflectionProvider->hasClass($className)) {
            // Likely an error in the configuration, return an error type
            return new ErrorType();
        }

        $className = $this->configurationResolver->resolveClassName($className);

        return $this->typeFactory->createExtensibleTypeFromType(new ObjectType($className));
    }
}
