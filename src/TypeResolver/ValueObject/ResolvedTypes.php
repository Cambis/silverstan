<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\ValueObject;

use PHPStan\Type\Type;

final class ResolvedTypes
{
    /**
     * @readonly
     */
    public Type $readableType;
    /**
     * @readonly
     */
    public Type $writableType;
    public function __construct(Type $readableType, Type $writableType)
    {
        $this->readableType = $readableType;
        $this->writableType = $writableType;
    }
}
