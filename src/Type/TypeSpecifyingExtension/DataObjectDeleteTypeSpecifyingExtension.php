<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\TypeSpecifyingExtension;

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
use PHPStan\Type\ObjectType;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRuleTest
 * @see \Cambis\Silverstan\Tests\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRuleTest
 */
final class DataObjectDeleteTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @var string[]
     */
    public const SUPPORTED_METHODS = [
        'delete',
    ];

    private TypeSpecifier $typeSpecifier;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
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

        /** @phpstan-ignore-next-line phpstanApi.instanceofType */
        if (!$objectType instanceof ObjectType) {
            return new SpecifiedTypes();
        }

        return $this->typeSpecifier->create(
            $node->var,
            $this->typeFactory->createUnsafeObjectTypeFromObjectType($objectType),
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
