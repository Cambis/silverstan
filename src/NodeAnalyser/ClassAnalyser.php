<?php

declare(strict_types=1);

namespace Cambis\Silverstan\NodeAnalyser;

use PHPStan\Reflection\ClassReflection;

final class ClassAnalyser
{
    public function isConfigurable(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
            return true;
        }

        return $classReflection->hasTraitUse('SilverStripe\Core\Config\Configurable');
    }

    public function isExtensible(ClassReflection $classReflection): bool
    {
        return $classReflection->hasTraitUse('SilverStripe\Core\Extensible');
    }
}
