<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection\ParameterReflection;

use Override;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;

final class ExtensibleParameterReflection implements ParameterReflection
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private Type $type;
    /**
     * @readonly
     */
    private PassedByReference $passedByReference;
    /**
     * @readonly
     */
    private bool $isOptional;
    /**
     * @readonly
     */
    private bool $isVariadic;
    /**
     * @readonly
     */
    private ?Type $defaultValue;
    public function __construct(string $name, Type $type, PassedByReference $passedByReference, bool $isOptional, bool $isVariadic, ?Type $defaultValue)
    {
        $this->name = $name;
        $this->type = $type;
        $this->passedByReference = $passedByReference;
        $this->isOptional = $isOptional;
        $this->isVariadic = $isVariadic;
        $this->defaultValue = $defaultValue;
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
