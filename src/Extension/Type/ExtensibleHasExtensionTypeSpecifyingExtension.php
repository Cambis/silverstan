<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Type;

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
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Type\ExtensibleHasExtensionTypeSpecifyingExtensionTest
 */
final class ExtensibleHasExtensionTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
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
        'hasExtension',
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

    public function getClass(): string
    {
        return $this->className;
    }

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
        return $this->typeSpecifier->create(
            $node->var,
            TypeCombinator::intersect(
                $this->typeFactory->createExtensibleTypeFromType($extensibleType),
                $extensionNameType->getClassStringObjectType(),
            ),
            TypeSpecifierContext::createTruthy(),
            true,
            $scope
        );
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
}
