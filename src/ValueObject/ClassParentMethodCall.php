<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class ClassParentMethodCall
{
    /**
     * @readonly
     */
    public string $className;
    /**
     * @readonly
     */
    public string $methodName;
    /**
     * @readonly
     */
    public bool $isFirstCall = false;
    public function __construct(string $className, string $methodName, bool $isFirstCall = false)
    {
        /**
         * @var class-string
         */
        $this->className = $className;
        $this->methodName = $methodName;
        $this->isFirstCall = $isFirstCall;
    }
}
