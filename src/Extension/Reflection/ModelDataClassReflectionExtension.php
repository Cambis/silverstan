<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\Reflection\ModelDataPropertyReflection;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

/**
 * This extension resolves `SilverStripe\Model\ModelData` magic properties.
 *
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ModelDataClassReflectionExtensionTest
 */
final readonly class ModelDataClassReflectionExtension implements PropertiesClassReflectionExtension
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

        if ($classReflection->is('SilverStripe\Model\ModelData')) {
            return true;
        }

        return $classReflection->isSubclassOf('SilverStripe\Model\ModelData');
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        if ($this->annotationClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return $this->annotationClassReflectionExtension->getProperty($classReflection, $propertyName);
        }

        $readableType = $classReflection->getNativeMethod('__get')->getVariants()[0]->getReturnType();
        $writableType = $classReflection->getNativeMethod('__set')->getVariants()[0]->getParameters()[1]->getType();

        return new ModelDataPropertyReflection($classReflection, $readableType, $writableType);
    }
}
