<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\TypeSpecifyingExtension;

use Cambis\Silverstan\Type\ObjectType\UnsafeObjectType;
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
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRuleTest
 * @see \Cambis\Silverstan\Tests\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRuleTest
 */
final class DataObjectWriteTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var string[]
     */
    public const SUPPORTED_METHODS = [
        'publishRecursive',
        'publishSingle',
        'write',
    ];

    private TypeSpecifier $typeSpecifier;

    public function __construct(
        private readonly TypeFactory $typeFactory
    ) {
    }

    #[Override]
    public function getClass(): string
    {
        return 'SilverStripe\ORM\DataObject';
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection, MethodCall $node, TypeSpecifierContext $context): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    #[Override]
    public function specifyTypes(MethodReflection $methodReflection, MethodCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
    {
        $objectType = $scope->getType($node->var);

        if (!$objectType instanceof UnsafeObjectType) {
            return new SpecifiedTypes();
        }

        return $this->typeSpecifier->create(
            $node->var,
            $this->typeFactory->createObjectTypeFromUnsafeObjectType($objectType),
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
