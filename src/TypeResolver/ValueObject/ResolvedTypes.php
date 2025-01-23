<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\ValueObject;

use PHPStan\Type\Type;

final readonly class ResolvedTypes
{
    public function __construct(
        public Type $readableType,
        public Type $writableType
    ) {
    }
}
