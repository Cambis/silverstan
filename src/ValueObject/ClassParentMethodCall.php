<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final readonly class ClassParentMethodCall
{
    public function __construct(
        /**
         * @var class-string
         */
        public string $className,
        public string $methodName,
        public bool $isFirstCall = false
    ) {
    }
}
