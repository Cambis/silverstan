<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver;

use Cambis\Silverstan\Reflection\ExtensibleMethodReflection;
use Cambis\Silverstan\Reflection\ExtensiblePropertyReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryProviderInterface;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use function array_key_exists;

final class ReflectionResolver
{
    /**
     * Local cache.
     *
     * @var array<string, array<string, PropertyReflection>>
     */
    private array $configurationPropertyReflections = [];

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly PropertyReflectionAnalyser $propertyReflectionAnalyser,
        private readonly ReflectionResolverRegistryProviderInterface $reflectionResolverRegistryProvider
    ) {
    }

    /**
     * Resolve all injected property reflections using the registered resolvers.
     *
     * @see \Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface
     * @return array<non-empty-string, PropertyReflection>
     */
    public function resolveInjectedPropertyReflections(ClassReflection $classReflection): array
    {
        $propertyReflections = [];

        foreach ($this->reflectionResolverRegistryProvider->getRegistry()->getPropertyReflectionResolvers() as $reflectionResolver) {
            $propertyReflections = [...$propertyReflections, ...$reflectionResolver->resolve($classReflection)];
        }

        return $propertyReflections;
    }

    /**
     * Resolve all injected method reflections using the registered resolvers.
     *
     * @see \Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface
     * @return array<non-empty-lowercase-string, MethodReflection>
     */
    public function resolveInjectedMethodReflections(ClassReflection $classReflection): array
    {
        $methodReflections = [];

        foreach ($this->reflectionResolverRegistryProvider->getRegistry()->getMethodReflectionResolvers() as $reflectionResolver) {
            $methodReflections = [...$methodReflections, ...$reflectionResolver->resolve($classReflection)];
        }

        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return $methodReflections;
        }

        return $methodReflections;
    }

    /**
     * Resolve a property that is included in the class phpdoc.
     */
    public function resolveAnnotationPropertyReflection(ClassReflection $classReflection, ClassReflection $declaringClass, string $propertyName): ?PropertyReflection
    {
        $propertyTags = $classReflection->getPropertyTags();

        if (array_key_exists($propertyName, $propertyTags)) {
            $propertyTag = $propertyTags[$propertyName];

            return new ExtensiblePropertyReflection(
                $declaringClass,
                $propertyTag->getReadableType() ?? new NeverType(),
                $propertyTag->getWritableType() ?? new NeverType(),
            );
        }

        foreach ($classReflection->getResolvedMixinTypes() as $mixinType) {
            foreach ($mixinType->getObjectClassReflections() as $mixinReflection) {
                $propertyReflection = $this->resolveAnnotationPropertyReflection($mixinReflection, $classReflection, $propertyName);

                if (!$propertyReflection instanceof PropertyReflection) {
                    continue;
                }

                return $propertyReflection;
            }
        }

        foreach ($classReflection->getAncestors() as $ancestorReflection) {
            // Ancestors includes the original class reflection itself
            if ($ancestorReflection === $classReflection) {
                continue;
            }

            if ($ancestorReflection->isTrait()) {
                $propertyReflection = $this->resolveAnnotationPropertyReflection($ancestorReflection, $classReflection, $propertyName);

                if (!$propertyReflection instanceof PropertyReflection) {
                    continue;
                }

                return $propertyReflection;
            }

            $propertyReflection = $this->resolveAnnotationPropertyReflection($ancestorReflection, $ancestorReflection, $propertyName);

            if (!$propertyReflection instanceof PropertyReflection) {
                continue;
            }

            return $propertyReflection;
        }

        return null;
    }

    /**
     * Resolve a method that is included in the class phpdoc. Does not support methods with parameters.
     */
    public function resolveAnnotationMethodReflection(ClassReflection $classReflection, ClassReflection $declaringClass, string $methodName): ?MethodReflection
    {
        $methodTags = $classReflection->getMethodTags();

        if (array_key_exists($methodName, $methodTags)) {
            $methodTag = $methodTags[$methodName];

            // Currently not supporting parameters
            if ($methodTag->getParameters() !== []) {
                return null;
            }

            return new ExtensibleMethodReflection(
                $methodName,
                $declaringClass,
                $methodTag->getReturnType(),
                [],
                false,
                false,
                null,
                TemplateTypeMap::createEmpty()
            );
        }

        foreach ($classReflection->getResolvedMixinTypes() as $mixinType) {
            foreach ($mixinType->getObjectClassReflections() as $mixinReflection) {
                $methodReflection = $this->resolveAnnotationMethodReflection($mixinReflection, $classReflection, $methodName);

                if (!$methodReflection instanceof MethodReflection) {
                    continue;
                }

                return $methodReflection;
            }
        }

        foreach ($classReflection->getAncestors() as $ancestorReflection) {
            // Ancestors includes the original class reflection itself
            if ($ancestorReflection === $classReflection) {
                continue;
            }

            if ($ancestorReflection->isTrait()) {
                $methodReflection = $this->resolveAnnotationMethodReflection($ancestorReflection, $classReflection, $methodName);

                if (!$methodReflection instanceof MethodReflection) {
                    continue;
                }

                return $methodReflection;
            }

            $methodReflection = $this->resolveAnnotationMethodReflection($ancestorReflection, $ancestorReflection, $methodName);

            if (!$methodReflection instanceof MethodReflection) {
                continue;
            }

            return $methodReflection;
        }

        return null;
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

        // Set up cache
        if (!array_key_exists($classReflection->getCacheKey(), $this->configurationPropertyReflections)) {
            $this->configurationPropertyReflections[$classReflection->getCacheKey()] = [];
        }

        // Check for cached result
        if (isset($this->configurationPropertyReflections[$classReflection->getCacheKey()][$propertyName])) {
            return $this->configurationPropertyReflections[$classReflection->getCacheKey()][$propertyName];
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

        // Fail, property is not a configuration property. Check the parent class next
        if (!$this->propertyReflectionAnalyser->isConfigurationProperty($property)) {
            return $this->resolveConfigurationPropertyReflection($classReflection->getParentClass(), $propertyName);
        }

        // Success! We have found a configuration property! Cache the result
        $this->configurationPropertyReflections[$classReflection->getCacheKey()][$propertyName] = $property;

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
