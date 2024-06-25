<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\ClassPropertiesNode;

use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use function str_contains;

/**
 * @see \Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\ConfigurationPropertiesExtensionTest
 */
final class ConfigurationPropertiesExtension implements ReadWritePropertiesExtension
{
    #[Override]
    public function isAlwaysRead(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->isConfigurationProperty($propertyReflection);
    }

    #[Override]
    public function isAlwaysWritten(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->isConfigurationProperty($propertyReflection);
    }

    #[Override]
    public function isInitialized(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->isConfigurationProperty($propertyReflection);
    }

    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return false;
        }

        return !$classReflection->hasTraitUse(Configurable::class);
    }

    private function isConfigurationProperty(PropertyReflection $propertyReflection): bool
    {
        $classReflection = $propertyReflection->getDeclaringClass();

        if ($this->shouldSkipClass($classReflection)) {
            return false;
        }

        if (!$propertyReflection->isPrivate()) {
            return false;
        }

        if (!$propertyReflection->isStatic()) {
            return false;
        }

        return !str_contains((string) $propertyReflection->getDocComment(), '@internal');
    }
}
