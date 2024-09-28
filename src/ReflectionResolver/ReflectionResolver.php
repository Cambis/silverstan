<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryProviderInterface;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Type;

/**
 * @api
 */
final readonly class ReflectionResolver
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private PropertyAnalyser $propertyAnalyser,
        private ReflectionResolverRegistryProviderInterface $reflectionResolverRegistryProvider
    ) {
    }

    /**
     * @return PropertyReflection[]
     */
    public function resolveInjectedPropertyReflections(ClassReflection $classReflection): array
    {
        $propertyReflections = [];

        foreach ($classReflection->getNativeReflection()->getProperties() as $reflectionProperty) {
            $property = $this->resolveConfigurationPropertyReflection($classReflection, $reflectionProperty->getName());

            if (!$property instanceof PropertyReflection) {
                continue;
            }

            $propertyReflections = [...$propertyReflections, ...$this->resolveInjectedPropertyReflectionsFromConfigurationProperty($classReflection, $reflectionProperty->getName())];
        }

        return $propertyReflections;
    }

    /**
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
        if (!$this->classAnalyser->isConfigurable($classReflection)) {
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
        if (!$this->propertyAnalyser->isConfigurationProperty($property)) {
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
