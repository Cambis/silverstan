<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection;

use Override;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;

final readonly class ExtensibleParameterReflection implements ParameterReflection
{
    public function __construct(
        private string $name,
        private Type $type,
        private PassedByReference $passedByReference,
        private bool $isOptional,
        private bool $isVariadic,
        private ?Type $defaultValue
    ) {
    }

    #[Override]
    public function getDefaultValue(): ?Type
    {
        return $this->defaultValue;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getType(): Type
    {
        return $this->type;
    }

    #[Override]
    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    #[Override]
    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    #[Override]
    public function passedByReference(): PassedByReference
    {
        return $this->passedByReference;
    }
}
