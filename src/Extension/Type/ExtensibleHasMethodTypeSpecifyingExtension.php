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
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Type\ExtensibleHasMethodTypeSpecifyingExtensionTest
 */
final class ExtensibleHasMethodTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'hasMethod',
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
        $methodNameType = $scope->getType($node->getArgs()[0]->value);

        if ($methodNameType->getConstantStrings() === []) {
            return new SpecifiedTypes();
        }

        $objectType = $scope->getType($node->var);

        if ($objectType->isObject()->no()) {
            return new SpecifiedTypes();
        }

        return $this->typeSpecifier->create(
            $node->var,
            new IntersectionType([
                $objectType,
                new HasMethodType($methodNameType->getConstantStrings()[0]->getValue()),
            ]),
            TypeSpecifierContext::createTruthy(),
            true,
            $scope
        );
    }

    #[Override]
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
