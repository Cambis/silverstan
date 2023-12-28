<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rules\Properties;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;

use function str_contains;

/**
 * @see \Cambis\Silverstan\Tests\Rules\Properties\ConfigurablePropertiesExtensionTest
 */
final class ConfigurablePropertiesExtension implements ReadWritePropertiesExtension
{
    public function isAlwaysRead(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return !$this->shouldSkipProperty($propertyReflection);
    }

    public function isAlwaysWritten(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return !$this->shouldSkipProperty($propertyReflection);
    }

    public function isInitialized(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return !$this->shouldSkipProperty($propertyReflection);
    }

    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return false;
        }

        return !$classReflection->hasTraitUse(Configurable::class);
    }

    private function shouldSkipProperty(PropertyReflection $propertyReflection): bool
    {
        $classReflection = $propertyReflection->getDeclaringClass();

        if ($this->shouldSkipClass($classReflection)) {
            return true;
        }

        if (!$propertyReflection->isPrivate()) {
            return true;
        }

        if (!$propertyReflection->isStatic()) {
            return true;
        }

        return str_contains((string) $propertyReflection->getDocComment(), '@internal');
    }
}
