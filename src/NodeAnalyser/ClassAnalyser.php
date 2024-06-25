<?php

declare(strict_types=1);

namespace Cambis\Silverstan\NodeAnalyser;

use PHPStan\Reflection\ClassReflection;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;

final class ClassAnalyser
{
    public function isConfigurable(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return true;
        }

        return $classReflection->hasTraitUse(Configurable::class);
    }
}
