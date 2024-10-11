<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\Reflection\ViewableDataPropertyReflection;
use Override;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ViewableDataClassReflectionExtensionTest
 */
final readonly class ViewableDataClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function __construct(
        private AnnotationsPropertiesClassReflectionExtension $annotationsPropertiesClassReflectionExtension,
    ) {
    }

    #[Override]
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        // Let PHPStan handle this case
        if ($classReflection->hasNativeProperty($propertyName)) {
            return false;
        }

        return $classReflection->isSubclassOf('SilverStripe\View\ViewableData');
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        if ($this->annotationsPropertiesClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return $this->annotationsPropertiesClassReflectionExtension->getProperty($classReflection, $propertyName);
        }

        $readableType = ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('__get')->getVariants())->getReturnType();
        $writableType = ParametersAcceptorSelector::selectSingle($classReflection->getNativeMethod('__set')->getVariants())->getParameters()[1]->getType();

        return new ViewableDataPropertyReflection($classReflection, $readableType, $writableType);
    }
}
