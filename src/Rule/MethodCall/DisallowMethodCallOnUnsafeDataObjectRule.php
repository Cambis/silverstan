<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\MethodCall;

use Cambis\Silverstan\Type\ObjectType\UnsafeObjectType;
use Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectDeleteTypeSpecifyingExtension;
use Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectExistsTypeSpecifyingExtension;
use Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectWriteTypeSpecifyingExtension;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function in_array;
use function property_exists;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 *
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRuleTest
 */
final readonly class DisallowMethodCallOnUnsafeDataObjectRule implements Rule
{
    /**
     * @var string[]
     */
    private const DEFAULT_ALLOWED_METHODS_CALLS = [
        ...DataObjectDeleteTypeSpecifyingExtension::SUPPORTED_METHODS,
        ...DataObjectExistsTypeSpecifyingExtension::SUPPORTED_METHODS,
        ...DataObjectWriteTypeSpecifyingExtension::SUPPORTED_METHODS,
    ];

    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.unsafeDataObjectAccess';

    public function __construct(
        /** @var string[] */
        private array $allowedMethodCalls = []
    ) {
    }

    #[Override]
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        if (in_array($node->name->toString(), self::DEFAULT_ALLOWED_METHODS_CALLS, true)) {
            return [];
        }

        if (in_array($node->name->toString(), $this->allowedMethodCalls, true)) {
            return [];
        }

        if (!$node->var instanceof MethodCall) {
            return [];
        }

        if ($node->var->name instanceof Expr) {
            return [];
        }

        $ownerType = $scope->getType($node->var->var);

        // Skip any native methods, we're only interested in magic ones
        foreach ($ownerType->getObjectClassReflections() as $classReflection) {
            if ($classReflection->hasNativeMethod($node->var->name->toString())) {
                return [];
            }
        }

        $type = $scope->getType($node->var);

        if (!$type instanceof UnsafeObjectType) {
            return [];
        }

        $varName = $this->resolveExprName($node->var->var);

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Accessing %s->%s()->%s() is potentially unsafe, as %s->%s() may not exist in the database. Call %s->%s()->exists() first to verify that it is safe to access.',
                    $varName,
                    $node->var->name->toString(),
                    $node->name->toString(),
                    $varName,
                    $node->var->name->toString(),
                    $varName,
                    $node->var->name->toString()
                )
            )
                ->tip('See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists')
                ->identifier(self::IDENTIFIER)
                ->build(),
        ];
    }

    private function resolveExprName(Expr $expr): string
    {
        if (!property_exists($expr, 'name')) {
            return '';
        }

        if ($expr->name instanceof Expr) {
            return '';
        }

        if ($expr instanceof MethodCall) {
            return sprintf('%s->%s()', $this->resolveExprName($expr->var), $expr->name->toString());
        }

        return '$' . $expr->name;
    }
}
