<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionAnalyser;

use PHPStan\Reflection\ClassReflection;

final class ClassReflectionAnalyser
{
    public function isConfigurable(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
            return true;
        }

        return $classReflection->hasTraitUse('SilverStripe\Core\Config\Configurable');
    }

    public function isDataObject(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf('SilverStripe\ORM\DataObject')) {
            return true;
        }

        return $classReflection->isSubclassOf('SilverStripe\Core\Extension');
    }

    public function isExtensible(ClassReflection $classReflection): bool
    {
        return $classReflection->hasTraitUse('SilverStripe\Core\Extensible');
    }
}
