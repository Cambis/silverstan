<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class ClassRequiredProperty
{
    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $className,
        /**
         * @var string[]
         */
        public readonly array $properties
    ) {
    }
}
