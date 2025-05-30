<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionAnalyser;

use PHPStan\Reflection\ClassReflection;

final class ClassReflectionAnalyser
{
    public function isConfigurable(ClassReflection $classReflection): bool
    {
        if ($classReflection->is('SilverStripe\Core\Extension')) {
            return true;
        }

        return $classReflection->hasTraitUse('SilverStripe\Core\Config\Configurable');
    }

    public function isDataObject(ClassReflection $classReflection): bool
    {
        if ($classReflection->is('SilverStripe\ORM\DataObject')) {
            return true;
        }

        return $classReflection->is('SilverStripe\Core\Extension');
    }

    public function isExtensible(ClassReflection $classReflection): bool
    {
        return $classReflection->hasTraitUse('SilverStripe\Core\Extensible');
    }

    public function isInjectable(ClassReflection $classReflection): bool
    {
        return $classReflection->hasTraitUse('SilverStripe\Core\Injector\Injectable');
    }
}
