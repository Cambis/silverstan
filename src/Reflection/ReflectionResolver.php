<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection;

use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;

final class ReflectionResolver
{
    public function __construct(
        private readonly PropertyAnalyser $propertyAnalyser,
    ) {
    }

    /**
     * If the property does not exist on the current class, loop over its parents until the property is found, otherwise return null.
     */
    public function resolveConfigurationProperty(?ClassReflection $classReflection, string $propertyName): ?PhpPropertyReflection
    {
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }

        // Fail, property does not exist. Check the parent class next.
        if (!$classReflection->hasNativeProperty($propertyName)) {
            return $this->resolveConfigurationProperty($classReflection->getParentClass(), $propertyName);
        }

        $property = $classReflection->getNativeProperty($propertyName);

        // Fail, property is not a configuration property. Check the parent class next.
        if (!$this->propertyAnalyser->isConfigurationProperty($property)) {
            return $this->resolveConfigurationProperty($classReflection->getParentClass(), $propertyName);
        }

        return $property;
    }
}
