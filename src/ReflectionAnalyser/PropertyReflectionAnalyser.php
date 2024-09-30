<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionAnalyser;

use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\PropertyReflection;
use function str_contains;

final class PropertyReflectionAnalyser
{
    public function isConfigurationProperty(ClassPropertyNode|PropertyReflection $property): bool
    {
        if (!$property->isPrivate()) {
            return false;
        }

        if (!$property->isStatic()) {
            return false;
        }

        if ($property instanceof ClassPropertyNode) {
            return !str_contains((string) $property->getPhpDoc(), '@internal');
        }

        return !str_contains((string) $property->getDocComment(), '@internal');
    }
}
