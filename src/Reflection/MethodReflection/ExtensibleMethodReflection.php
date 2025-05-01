<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Reflection\MethodReflection;

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

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    /**
     * @return list<ParametersAcceptor>
     */
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

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getThrowType(): ?Type
    {
        return $this->throwType;
    }

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
