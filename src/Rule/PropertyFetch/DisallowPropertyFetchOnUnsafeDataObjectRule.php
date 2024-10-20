<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\PropertyFetch;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\NodeVisitor\PropertyFetchAssignedToVisitor;
use Cambis\Silverstan\Type\UnsafeObjectType;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function property_exists;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<PropertyFetch>
 *
 * @see \Cambis\Silverstan\Tests\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRuleTest
 */
final class DisallowPropertyFetchOnUnsafeDataObjectRule implements SilverstanRuleInterface
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Call `exists()` first before accessing any magic `\SilverStripe\ORM\DataObject` properties as the object may not be present in the database. ' .
            'Property assignment is allowed.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @method \SilverStripe\ORM\DataObject Bar()
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function doSomething(): string
    {
        return $this->Bar()->Title;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * @method \SilverStripe\ORM\DataObject Bar()
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function doSomething(): string
    {
        if (!$this->Bar()->exists()) {
            return '';
        }

        return $this->Bar()->Title;
    }
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                    ]
                )],
        );
    }

    #[Override]
    public function getNodeType(): string
    {
        return PropertyFetch::class;
    }

    /**
     * @param PropertyFetch $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        // Allow access if the node is being assigned
        if ($node->hasAttribute(PropertyFetchAssignedToVisitor::ATTRIBUTE_KEY)) {
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
                    'Accessing %s->%s()->%s is potentially unsafe, as %s->%s() may not exist in the database. Call %s->%s()->exists() first to verify that it is safe to access.',
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
                ->identifier('silverstan.unsafeDataObjectAccess')
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
