<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\MethodCall;

use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use SilverStripe\ORM\DataObject;

/**
 * @see \Cambis\Silverstan\Tests\Extension\MethodCall\MagicDataObjectMethodReturnTypeExtensionTest
 */
final class MagicDataObjectMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    #[Override]
    public function getClass(): string
    {
        return DataObject::class;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        $declaringClass = $methodReflection->getDeclaringClass();

        if ($declaringClass->hasNativeMethod($methodReflection->getName())) {
            return false;
        }

        return $declaringClass->hasMethod($methodReflection->getName());
    }

    #[Override]
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $type = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

        if ((new ObjectType(DataObject::class))->isSuperTypeOf($type)->no()) {
            return null;
        }

        return TypeCombinator::addNull($type);
    }
}
