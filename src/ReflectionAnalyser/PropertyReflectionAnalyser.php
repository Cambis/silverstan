<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionAnalyser;

use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\PropertyReflection;

final class PropertyReflectionAnalyser
{
    /**
     * @param ClassPropertyNode|PropertyReflection $property
     */
    public function isConfigurationProperty($property): bool
    {
        if (!$property->isPrivate()) {
            return false;
        }

        if (!$property->isStatic()) {
            return false;
        }

        if ($property instanceof ClassPropertyNode) {
            return strpos((string) $property->getPhpDoc(), '@internal') === false;
        }

        return strpos((string) $property->getDocComment(), '@internal') === false;
    }
}
