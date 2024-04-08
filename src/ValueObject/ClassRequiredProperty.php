<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final readonly class ClassRequiredProperty
{
    public function __construct(
        /**
         * @var class-string
         */
        public string $className,
        /**
         * @var string[]
         */
        public array $properties
    ) {
    }
}
