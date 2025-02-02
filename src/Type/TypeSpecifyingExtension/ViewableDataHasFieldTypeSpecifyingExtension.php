<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\TypeSpecifyingExtension;

use Override;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\NullType;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\ViewableDataHasFieldTypeSpecifyingExtensionTest
 */
final class ViewableDataHasFieldTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'hasField',
    ];

    private TypeSpecifier $typeSpecifier;

    public function __construct(
        /**
         * @var class-string
         */
        private readonly string $className
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
        if (!in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true)) {
            return false;
        }

        return $context->falsey();
    }

    #[Override]
    public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
    {
        $propertyNameType = $scope->getType($node->getArgs()[0]->value);

        if ($propertyNameType->isString()->no()) {
            return new SpecifiedTypes();
        }

        if ($propertyNameType->getConstantStrings() === []) {
            return new SpecifiedTypes();
        }

        $propertyFetch = new PropertyFetch($node->var, $propertyNameType->getConstantStrings()[0]->getValue());

        return $this->typeSpecifier->create(
            $propertyFetch,
            new NullType(),
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
