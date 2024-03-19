<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class RequiredParentCall
{
    public function __construct(
        /**
         * @var class-string
         */
        private readonly string $className,
        private readonly string $methodName,
        private readonly bool $isFirstCall = false
    ) {
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getIsFirstCall(): bool
    {
        return $this->isFirstCall;
    }
}
