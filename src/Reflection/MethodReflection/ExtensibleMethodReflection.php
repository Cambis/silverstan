<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection\MethodReflection;

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

final class ExtensibleMethodReflection implements MethodReflection
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private ClassReflection $declaringClass;
    /**
     * @readonly
     */
    private Type $returnType;
    /**
     * @var list<ParameterReflection>
     * @readonly
     */
    private array $parameters;
    /**
     * @readonly
     */
    private bool $isStatic;
    /**
     * @readonly
     */
    private bool $isVariadic;
    /**
     * @readonly
     */
    private ?Type $throwType;
    /**
     * @readonly
     */
    private TemplateTypeMap $templateTypeMap;
    /**
     * @param list<ParameterReflection> $parameters
     */
    public function __construct(string $name, ClassReflection $declaringClass, Type $returnType, array $parameters, bool $isStatic, bool $isVariadic, ?Type $throwType, TemplateTypeMap $templateTypeMap)
    {
        $this->name = $name;
        $this->declaringClass = $declaringClass;
        $this->returnType = $returnType;
        $this->parameters = $parameters;
        $this->isStatic = $isStatic;
        $this->isVariadic = $isVariadic;
        $this->throwType = $throwType;
        $this->templateTypeMap = $templateTypeMap;
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
     * @return list<ParametersAcceptor>
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
