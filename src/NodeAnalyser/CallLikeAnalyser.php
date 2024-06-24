<?php

declare(strict_types=1);

namespace Cambis\Silverstan\NodeAnalyser;

use PHPStan\Reflection\ParameterReflection;

final class CallLikeAnalyser
{
    /**
     * @param ParameterReflection[] $parameters
     */
    public function areLiteralParametersPassedByReference(array $parameters): bool
    {
        foreach ($parameters as $parameter) {
            if ($parameter->getType()->isObject()->yes()) {
                continue;
            }

            if ($parameter->passedByReference()->no()) {
                return false;
            }
        }

        return true;
    }
}
