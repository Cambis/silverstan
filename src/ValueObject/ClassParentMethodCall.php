<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final readonly class ClassParentMethodCall
{
    public function __construct(
        /**
         * @var class-string
         */
        private string $className,
        private string $methodName,
        private bool $isFirstCall = false
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
