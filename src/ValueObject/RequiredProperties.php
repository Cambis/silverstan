<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final class RequiredProperties
{
    public function __construct(
        /**
         * @var class-string
         */
        private readonly string $className,
        /**
         * @var string[]
         */
        private readonly array $properties,
    ) {
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
