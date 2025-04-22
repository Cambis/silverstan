<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final readonly class ClassAllowedMethodCall
{
    public function __construct(
        /**
         * @var class-string
         */
        public string $className,
        /**
         * @var list<string>
         */
        public array $methodNames,
    ) {
    }
}
