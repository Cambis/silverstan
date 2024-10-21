<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class ClassParentMethodCall
{
    public function __construct(
        /**
         * @var class-string
         */
        public readonly string $className,
        public readonly string $methodName,
        public readonly bool $isFirstCall = false
    ) {
    }
}
