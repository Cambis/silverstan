<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeComparator;

use PHPStan\Type\Type;
use function count;

final class CallLikeTypeComparator
{
    /**
     * @param Type[] $firstParametersTypes
     * @param Type[] $secondParametersTypes
     */
    public function doSignaturesMatch(array $firstParametersTypes, array $secondParametersTypes): bool
    {
        if (count($firstParametersTypes) !== count($secondParametersTypes)) {
            return false;
        }

        $counter = count($firstParametersTypes);

        for ($i = 0; $i < $counter; $i++) {
            $firstType = $firstParametersTypes[$i];
            $secondType = $secondParametersTypes[$i];

            if ($firstType->isSuperTypeOf($secondType)->no()) {
                return false;
            }
        }

        return true;
    }
}
