<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Type;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Type\ExtensibleHasExtensionTypeSpecifyingExtensionTest
 */
final class ExtensibleHasExtensionTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'hasExtension',
    ];

    private TypeSpecifier $typeSpecifier;

    public function __construct(
        /** @var class-string */
        private readonly string $className,
        private readonly ClassAnalyser $classAnalyser,
    ) {
    }

    #[Override]
    public function getClass(): string
    {
        return $this->className;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
    {
        if (!$this->classAnalyser->isExtensible($methodReflection->getDeclaringClass())) {
            return false;
        }

        if (!in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true)) {
            return false;
        }

        return $context->truthy();
    }

    #[Override]
    public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
    {
        $expr = $node->getArgs()[0]->value;
        $extensionNameType = $scope->getType($expr);

        if ($extensionNameType->isClassStringType()->no()) {
            return new SpecifiedTypes();
        }

        $extensibleType = $scope->getType($node->var);

        if ($extensibleType->isObject()->no()) {
            return new SpecifiedTypes();
        }

        $type = new IntersectionType([$extensibleType, $extensionNameType->getClassStringObjectType()]);

        return $this->typeSpecifier->create($node->var, $type, TypeSpecifierContext::createTruthy(), true, $scope);
    }

    #[Override]
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
