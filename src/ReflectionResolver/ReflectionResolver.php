<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryProviderInterface;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Type;
use ReflectionProperty;

final readonly class ReflectionResolver
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private PropertyReflectionAnalyser $propertyReflectionAnalyser,
        private ReflectionResolverRegistryProviderInterface $reflectionResolverRegistryProvider
    ) {
    }

    /**
     * Resolve all injected property reflections using the registered resolvers.
     *
     * @see \Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface
     * @return PropertyReflection[]
     */
    public function resolveInjectedPropertyReflections(ClassReflection $classReflection): array
    {
        $propertyReflections = [];

        foreach ($classReflection->getNativeReflection()->getProperties(ReflectionProperty::IS_PRIVATE) as $reflectionProperty) {
            $property = $this->resolveConfigurationPropertyReflection($classReflection, $reflectionProperty->getName());

            if (!$property instanceof PropertyReflection) {
                continue;
            }

            $propertyReflections = [...$propertyReflections, ...$this->resolveInjectedPropertyReflectionsFromConfigurationProperty($classReflection, $reflectionProperty->getName())];
        }

        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return $propertyReflections;
        }

        return [
            ...$propertyReflections,
            ...$this->resolveInjectedPropertyReflections($classReflection->getParentClass()),
        ];
    }

    /**
     * Resolve injected property reflections from a configuration property using the registered resolvers.
     *
     * @see \Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface
     * @return PropertyReflection[]
     */
    public function resolveInjectedPropertyReflectionsFromConfigurationProperty(ClassReflection $classReflection, string $propertyName): array
    {
        foreach ($this->reflectionResolverRegistryProvider->getRegistry()->getPropertyReflectionResolvers() as $reflectionResolver) {
            if ($reflectionResolver->getConfigurationPropertyName() !== $propertyName) {
                continue;
            }

            return $reflectionResolver->resolve($classReflection);
        }

        return [];
    }

    /**
     * Resolve all injected method reflections using the registered resolvers.
     *
     * @see \Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface
     * @return MethodReflection[]
     */
    public function resolveInjectedMethodReflections(ClassReflection $classReflection): array
    {
        $methodReflections = [];

        foreach ($classReflection->getNativeReflection()->getProperties(ReflectionProperty::IS_PRIVATE) as $reflectionProperty) {
            $property = $this->resolveConfigurationPropertyReflection($classReflection, $reflectionProperty->getName());

            if (!$property instanceof PropertyReflection) {
                continue;
            }

            $methodReflections = [...$methodReflections, ...$this->resolveInjectedMethodReflectionsFromConfigurationProperty($classReflection, $reflectionProperty->getName())];
        }

        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return $methodReflections;
        }

        return [
            ...$methodReflections,
            ...$this->resolveInjectedMethodReflections($classReflection->getParentClass()),
        ];
    }

    /**
     * Resolve injected method reflections from a configuration property using the registered resolvers.
     *
     * @see \Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface
     * @return MethodReflection[]
     */
    public function resolveInjectedMethodReflectionsFromConfigurationProperty(ClassReflection $classReflection, string $propertyName): array
    {
        foreach ($this->reflectionResolverRegistryProvider->getRegistry()->getMethodReflectionResolvers() as $reflectionResolver) {
            if ($reflectionResolver->getConfigurationPropertyName() !== $propertyName) {
                continue;
            }

            return $reflectionResolver->resolve($classReflection);
        }

        return [];
    }

    /**
     * Attempt to resolve a configuration property. Properties are resolved in the following order:
     *  - from `@mixin` annotations
     *  - in the current class
     *  - from the parent classes
     */
    public function resolveConfigurationPropertyReflection(?ClassReflection $classReflection, string $propertyName): ?PropertyReflection
    {
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }

        // Safety check, only configurable classes can have configuration properties
        if (!$this->classReflectionAnalyser->isConfigurable($classReflection)) {
            return null;
        }

        // Resolve the property from the mixins first
        $property = $this->resolveConfigurationPropertyReflectionFromMixins($classReflection->getResolvedMixinTypes(), $propertyName);

        // Fail, property does not exist. Check the current class next
        if (!$property instanceof PropertyReflection && $classReflection->hasNativeProperty($propertyName)) {
            $property = $classReflection->getNativeProperty($propertyName);
        }

        // Fail, property does not exist. Check the parent class next
        if (!$property instanceof PropertyReflection) {
            return $this->resolveConfigurationPropertyReflection($classReflection->getParentClass(), $propertyName);
        }

        // Fail, property is not a configuration property. Check the parent class next.
        if (!$this->propertyReflectionAnalyser->isConfigurationProperty($property)) {
            return $this->resolveConfigurationPropertyReflection($classReflection->getParentClass(), $propertyName);
        }

        // Success! We have found a configuration property!
        return $property;
    }

    /**
     * @param Type[] $mixinTypes
     */
    private function resolveConfigurationPropertyReflectionFromMixins(array $mixinTypes, string $propertyName): ?PropertyReflection
    {
        foreach ($mixinTypes as $type) {
            if ($type->isObject()->no()) {
                continue;
            }

            if ($type->getObjectClassReflections() === []) {
                continue;
            }

            $property = $this->resolveConfigurationPropertyReflection(
                $type->getObjectClassReflections()[0],
                $propertyName
            );

            if (!$property instanceof PropertyReflection) {
                continue;
            }

            return $property;
        }

        return null;
    }
}
