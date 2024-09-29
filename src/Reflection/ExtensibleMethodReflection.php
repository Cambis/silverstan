<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection;

use Override;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

final readonly class ExtensibleMethodReflection implements MethodReflection
{
    public function __construct(
        private string $name,
        private ClassReflection $classReflection,
        private Type $returnType
    ) {
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
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    /**
     * @return ParametersAcceptor[]
     */
    #[Override]
    public function getVariants(): array
    {
        return [
            new FunctionVariant(
                TemplateTypeMap::createEmpty(),
                TemplateTypeMap::createEmpty(),
                [],
                false,
                $this->returnType
            ),
        ];
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
    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    #[Override]
    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    #[Override]
    public function getThrowType(): ?Type
    {
        return null;
    }

    #[Override]
    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}