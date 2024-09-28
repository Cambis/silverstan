<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\Reflection\ExtensiblePropertyReflection;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\NullType;
use function array_key_exists;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ViewableDataClassReflectionExtensionTest
 */
final class ViewableDataClassReflectionExtension implements PropertiesClassReflectionExtension
{
    #[Override]
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        // Let PHPStan handle this case
        if ($classReflection->hasNativeProperty($propertyName)) {
            return false;
        }

        // Let PHPStan handle this case
        if (array_key_exists($propertyName, $classReflection->getPropertyTags())) {
            return false;
        }

        return $classReflection->isSubclassOf('SilverStripe\View\ViewableData');
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new ExtensiblePropertyReflection($classReflection, new NullType(), new NullType());
    }
}
