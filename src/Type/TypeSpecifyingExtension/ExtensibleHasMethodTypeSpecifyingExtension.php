<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\TypeSpecifyingExtension;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\ExtensibleHasMethodTypeSpecifyingExtensionTest
 */
final class ExtensibleHasMethodTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @readonly
     */
    private string $className;
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'hasMethod',
    ];

    private TypeSpecifier $typeSpecifier;

    public function __construct(string $className, ClassReflectionAnalyser $classReflectionAnalyser, TypeFactory $typeFactory)
    {
        /**
         * @var class-string
         */
        $this->className = $className;
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->typeFactory = $typeFactory;
    }

    #[Override]
    public function getClass(): string
    {
        return $this->className;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
    {
        if (!$this->classReflectionAnalyser->isExtensible($methodReflection->getDeclaringClass())) {
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

        $extensibleType = $scope->getType($node->var);

        if ($extensibleType->isObject()->no()) {
            return new SpecifiedTypes();
        }

        return $this->typeSpecifier->create(
            $node->var,
            TypeCombinator::intersect(
                $this->typeFactory->createExtensibleTypeFromType($extensibleType),
                new HasMethodType($methodNameType->getConstantStrings()[0]->getValue()),
            ),
            TypeSpecifierContext::createTruthy(),
            $scope
        )->setAlwaysOverwriteTypes();
    }

    #[Override]
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
