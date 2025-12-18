<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection\PropertyReflection;

use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

final class ViewableDataPropertyReflection implements PropertyReflection
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

    #[Override]
    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    #[Override]
    public function isStatic(): bool
    {
        return false;
    }

    #[Override]
    public function isPrivate(): bool
    {
        return false;
    }

    #[Override]
    public function isPublic(): bool
    {
        return true;
    }

    #[Override]
    public function getDocComment(): ?string
    {
        return null;
    }

    #[Override]
    public function getReadableType(): Type
    {
        return $this->readableType;
    }

    #[Override]
    public function getWritableType(): Type
    {
        return $this->writableType;
    }

    #[Override]
    public function canChangeTypeAfterAssignment(): bool
    {
        return true;
    }

    #[Override]
    public function isReadable(): bool
    {
        return true;
    }

    #[Override]
    public function isWritable(): bool
    {
        return true;
    }

    #[Override]
    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    #[Override]
    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    #[Override]
    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}
