<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ValueObject;

final readonly class RequiredProperties
{
    public function __construct(
        /**
         * @var class-string
         */
        private string $className,
        /**
         * @var string[]
         */
        private array $properties,
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
