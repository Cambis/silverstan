<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection;

use Override;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;

final readonly class ExtensibleMethodReflection implements MethodReflection
{
    /**
     * @param list<ParameterReflection> $parameters
     */
    public function __construct(
        private string $name,
        private ClassReflection $declaringClass,
        private Type $returnType,
        private array $parameters,
        private bool $isStatic,
        private bool $isVariadic,
        private ?Type $throwType,
        private TemplateTypeMap $templateTypeMap,
    ) {
    }

    #[Override]
    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    #[Override]
    public function isStatic(): bool
    {
        return $this->isStatic;
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
                $this->templateTypeMap,
                null,
                $this->parameters,
                $this->isVariadic,
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
        return $this->throwType;
    }

    #[Override]
    public function hasSideEffects(): TrinaryLogic
    {
        if ($this->returnType->isVoid()->yes()) {
            return TrinaryLogic::createYes();
        }

        if ((new ThisType($this->declaringClass))->isSuperTypeOf($this->returnType)->yes()) {
            return TrinaryLogic::createYes();
        }

        return TrinaryLogic::createMaybe();
    }
}
