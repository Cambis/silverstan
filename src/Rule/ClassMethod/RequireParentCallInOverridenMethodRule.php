<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassMethod;

use Cambis\Silverstan\ValueObject\ClassParentMethodCall;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<ClassMethod>
 *
 * @see \Cambis\Silverstan\Tests\Rule\ClassMethod\RequireParentCallInOverridenMethodRuleTest
 */
final class RequireParentCallInOverridenMethodRule implements Rule
{
    /**
     * @readonly
     */
    private NodeFinder $nodeFinder;
    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.requiredParentCall';

    /**
     * @var ClassParentMethodCall[]
     */
    private array $classParentMethodCalls;

    /**
     * @param array<array{class: class-string, method: string, isFirst?: bool}> $classes
     */
    public function __construct(
        NodeFinder $nodeFinder,
        array $classes
    ) {
        $this->nodeFinder = $nodeFinder;
        foreach ($classes as $classParentCall) {
            $this->classParentMethodCalls[] = new ClassParentMethodCall(
                $classParentCall['class'],
                $classParentCall['method'],
                $classParentCall['isFirst'] ?? false
            );
        }
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        $classParentMethodCall = $this->getClassParentMethodCall($node, $classReflection);
        if (!$classParentMethodCall instanceof ClassParentMethodCall) {
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
                        $classParentMethodCall->methodName,
                        $classParentMethodCall->methodName
                    )
                )
                    ->identifier(self::IDENTIFIER)
                    ->build(),
            ];
        }
        // Check if we have the required call
        if (!$this->hasClassParentMethodCall($nodes, $classParentMethodCall)) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() is missing required call to parent::%s().',
                        $classReflection->getDisplayName(),
                        $classParentMethodCall->methodName,
                        $classParentMethodCall->methodName
                    )
                )
                    ->identifier(self::IDENTIFIER)
                    ->build(),
            ];
        }
        // Extra condition if the parent call should come first
        if (!$classParentMethodCall->isFirstCall) {
            return [];
        }
        $firstCall = $nodes[0];
        if (
            !$firstCall instanceof StaticCall ||
            ($firstCall->name instanceof Identifier && $firstCall->name->toString() !== $classParentMethodCall->methodName)
        ) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() should call parent::%s() first.',
                        $classReflection->getDisplayName(),
                        $classParentMethodCall->methodName,
                        $classParentMethodCall->methodName
                    )
                )
                    ->identifier(self::IDENTIFIER)
                    ->build(),
            ];
        }
        return [];
    }

    private function getClassParentMethodCall(ClassMethod $classMethod, ClassReflection $classReflection): ?ClassParentMethodCall
    {
        foreach ($this->classParentMethodCalls as $requiredParentCall) {
            if (!$classReflection->is($requiredParentCall->className)) {
                continue;
            }

            if ($requiredParentCall->methodName !== $classMethod->name->toString()) {
                continue;
            }

            return $requiredParentCall;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     */
    private function hasClassParentMethodCall(array $nodes, ClassParentMethodCall $requiredParentCall): bool
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

            if ($node->name->toString() !== $requiredParentCall->methodName) {
                continue;
            }

            return true;
        }

        return false;
    }
}
