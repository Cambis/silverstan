<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\MethodCall;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\Extension\Type\DataObjectDeleteTypeSpecifyingExtension;
use Cambis\Silverstan\Extension\Type\DataObjectExistsTypeSpecifyingExtension;
use Cambis\Silverstan\Extension\Type\DataObjectWriteTypeSpecifyingExtension;
use Cambis\Silverstan\Type\UnsafeObjectType;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function in_array;
use function property_exists;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<MethodCall>
 *
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRuleTest
 */
final class DisallowMethodCallOnUnsafeDataObjectRule implements SilverstanRuleInterface
{
    /**
     * @var string[]
     */
    private const DEFAULT_ALLOWED_METHODS_CALLS = [
        ...DataObjectDeleteTypeSpecifyingExtension::SUPPORTED_METHODS,
        ...DataObjectExistsTypeSpecifyingExtension::SUPPORTED_METHODS,
        ...DataObjectWriteTypeSpecifyingExtension::SUPPORTED_METHODS,
    ];

    public function __construct(
        /** @var string[] */
        private readonly array $allowedMethodCalls = []
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Call `exists()` first before accessing any magic `SilverStripe\ORM\DataObject` methods as the object may not be present in the database. ' .
            'Database manipulation methods such as `write()` and `delete()` are allowed by default. ' .
            'If you think a method is safe to call by default add it to the `allowedMethodCalls` configuration.',
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
        return $this->Bar()->doSomething();
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

        return $this->Bar()->doSomething();
    }
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'allowedMethodCalls' => [
                            'mySafeMethod',
                        ],
                    ]
                )],
        );
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
