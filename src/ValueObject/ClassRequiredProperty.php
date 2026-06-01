<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class ClassRequiredProperty
{
    /**
     * @readonly
     */
    public string $className;
    /**
     * @readonly
     */
    public array $properties;
    public function __construct(string $className, array $properties)
    {
        /**
         * @var class-string
         */
        $this->className = $className;
        /**
         * @var string[]
         */
        $this->properties = $properties;
    }
}
