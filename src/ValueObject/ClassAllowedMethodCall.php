<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class ClassAllowedMethodCall
{
    /**
     * @readonly
     */
    public string $className;
    /**
     * @readonly
     */
    public array $methodNames;
    public function __construct(string $className, array $methodNames)
    {
        /**
         * @var class-string
         */
        $this->className = $className;
        /**
         * @var list<string>
         */
        $this->methodNames = $methodNames;
    }
}
