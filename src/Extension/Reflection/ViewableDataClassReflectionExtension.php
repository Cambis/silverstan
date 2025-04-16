<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\Reflection\ViewableDataPropertyReflection;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

/**
 * This extension resolves `SilverStripe\View\ViewableData` and `SilverStripe\Model\ModelData` magic properties.
 *
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ViewableDataClassReflectionExtensionTest
 */
final readonly class ViewableDataClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function __construct(
        private AnnotationClassReflectionExtension $annotationClassReflectionExtension
    ) {
    }

    #[Override]
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        // Let PHPStan handle this case
        if ($classReflection->hasNativeProperty($propertyName)) {
            return false;
        }

        // silverstripe/framework <= 6.x
        if ($classReflection->is('SilverStripe\View\ViewableData')) {
            return true;
        }

        // silverstripe/framework >= 6.x
        return $classReflection->is('SilverStripe\Model\ModelData');
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        if ($this->annotationClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return $this->annotationClassReflectionExtension->getProperty($classReflection, $propertyName);
        }

        $readableType = $classReflection->getNativeMethod('__get')->getVariants()[0]->getReturnType();
        $writableType = $classReflection->getNativeMethod('__set')->getVariants()[0]->getParameters()[1]->getType();

        return new ViewableDataPropertyReflection($classReflection, $readableType, $writableType);
    }
}
