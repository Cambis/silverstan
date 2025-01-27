<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

final class ExtensiblePropertyReflection implements PropertyReflection
{
    /**
     * @readonly
     */
    private ClassReflection $classReflection;
    /**
     * @readonly
     */
    private Type $readableType;
    /**
     * @readonly
     */
    private Type $writableType;
    public function __construct(ClassReflection $classReflection, Type $readableType, Type $writableType)
    {
        $this->classReflection = $classReflection;
        $this->readableType = $readableType;
        $this->writableType = $writableType;
    }
    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getReadableType(): Type
    {
        return $this->readableType;
    }

    public function getWritableType(): Type
    {
        return $this->writableType;
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return $this->readableType->equals($this->writableType);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}
