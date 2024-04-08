<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassMethod;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ValueObject\RequiredParentCall;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\ORM\DataObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

use function sprintf;

/**
 * @implements SilverstanRuleInterface<ClassMethod>
 *
 * @see \Cambis\Silverstan\Tests\Rule\ClassMethod\RequireParentCallInOverridenMethodRuleTest
 */
final class RequireParentCallInOverridenMethodRule implements SilverstanRuleInterface
{
    /**
     * @var RequiredParentCall[]
     */
    private array $requiredParentCalls;

    /**
     * @param array<array{class: class-string, method: string, isFirst?: bool}> $requiredParentCalls
     */
    public function __construct(
        private readonly NodeFinder $nodeFinder,
        array $requiredParentCalls = []
    ) {
        foreach ($requiredParentCalls as $requiredParentCall) {
            Assert::keyExists($requiredParentCall, 'class');
            Assert::keyExists($requiredParentCall, 'method');
            Assert::string($requiredParentCall['class']);
            Assert::string($requiredParentCall['method']);

            $this->requiredParentCalls[] = new RequiredParentCall(
                $requiredParentCall['class'],
                $requiredParentCall['method'],
                $requiredParentCall['isFirst'] ?? false
            );
        }
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require parent call in an overriden method.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
    }
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'requiredParentCalls' => [
                            [
                                'class' => DataObject::class,
                                'method' => 'onBeforeWrite',
                                'isFirst' => false,
                            ],
                        ],
                    ]
                )],
        );
    }

    #[Override]
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        $requiredParentCall = $this->getRequiredParentCall($node, $classReflection);

        if (!$requiredParentCall instanceof RequiredParentCall) {
            return [];
        }

        // Get all nodes that aren't an expression
        $nodes = $this->nodeFinder->find((array) $node->stmts, static function (Node $node): bool {
            return !$node instanceof Expression;
        });

        // If there are no calls, return an error
        if ($nodes === []) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() is missing required call to parent::%s().',
                        $classReflection->getDisplayName(),
                        $requiredParentCall->getMethodName(),
                        $requiredParentCall->getMethodName()
                    )
                )
                ->build(),
            ];
        }

        // Check if we have the required call
        if (!$this->hasRequiredParentCall($nodes, $requiredParentCall)) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() is missing required call to parent::%s().',
                        $classReflection->getDisplayName(),
                        $requiredParentCall->getMethodName(),
                        $requiredParentCall->getMethodName()
                    )
                )
                ->build(),
            ];
        }
       
        // Extra condition if the parent call should come first
        if (!$requiredParentCall->getIsFirstCall()) {
            return [];
        }

        $firstCall = $nodes[0];

        if (
            !$firstCall instanceof StaticCall ||
            ($firstCall->name instanceof Identifier && $firstCall->name->toString() !== $requiredParentCall->getMethodName())
        ) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() should call parent::%s() first.',
                        $classReflection->getDisplayName(),
                        $requiredParentCall->getMethodName(),
                        $requiredParentCall->getMethodName()
                    )
                )
                ->build(),
            ];
        }

        return [];
    }

    private function getRequiredParentCall(ClassMethod $classMethod, ClassReflection $classReflection): ?RequiredParentCall
    {
        foreach ($this->requiredParentCalls as $requiredParentCall) {
            if (!$classReflection->isSubclassOf($requiredParentCall->getClassName())) {
                continue;
            }

            if ($requiredParentCall->getMethodName() !== $classMethod->name->toString()) {
                continue;
            }

            return $requiredParentCall;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     */
    private function hasRequiredParentCall(array $nodes, RequiredParentCall $requiredParentCall): bool
    {
        foreach ($nodes as $node) {
            if (!$node instanceof StaticCall) {
                continue;
            }

            if (!$node->class instanceof Name) {
                continue;
            }

            if (!$node->name instanceof Identifier) {
                continue;
            }

            if ($node->class->toString() !== 'parent') {
                continue;
            }

            if ($node->name->toString() !== $requiredParentCall->getMethodName()) {
                continue;
            }

            return true;
        }

        return false;
    }
}
