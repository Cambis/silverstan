<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\Type;

final readonly class ReflectionResolver
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private PropertyAnalyser $propertyAnalyser,
    ) {
    }

    /**
     * Attempt to resolve a configuration property. Properties are resolved in the following order:
     *  - from `@mixin` annotations
     *  - in the current class
     *  - from the parent classes
     */
    public function resolveConfigurationProperty(?ClassReflection $classReflection, string $propertyName): ?PhpPropertyReflection
    {
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }

        // Safety check, only configurable classes can have configuration properties
        if (!$this->classAnalyser->isConfigurable($classReflection)) {
            return null;
        }

        // Resolve the property from the mixins first
        $property = $this->resolveConfigurationPropertyFromMixins($classReflection->getResolvedMixinTypes(), $propertyName);

        // Fail, property does not exist. Check the current class next
        if (!$property instanceof PhpPropertyReflection && $classReflection->hasNativeProperty($propertyName)) {
            $property = $classReflection->getNativeProperty($propertyName);
        }

        // Fail, property does not exist. Check the parent class next
        if (!$property instanceof PhpPropertyReflection) {
            return $this->resolveConfigurationProperty($classReflection->getParentClass(), $propertyName);
        }

        // Fail, property is not a configuration property. Check the parent class next.
        if (!$this->propertyAnalyser->isConfigurationProperty($property)) {
            return $this->resolveConfigurationProperty($classReflection->getParentClass(), $propertyName);
        }

        // Success! We have found a configuration property!
        return $property;
    }

    /**
     * @param Type[] $mixinTypes
     */
    private function resolveConfigurationPropertyFromMixins(array $mixinTypes, string $propertyName): ?PhpPropertyReflection
    {
        foreach ($mixinTypes as $type) {
            if ($type->isObject()->no()) {
                continue;
            }

            if ($type->getObjectClassReflections() === []) {
                continue;
            }

            $property = $this->resolveConfigurationProperty(
                $type->getObjectClassReflections()[0],
                $propertyName
            );

            if (!$property instanceof PhpPropertyReflection) {
                continue;
            }

            return $property;
        }

        return null;
    }
}
