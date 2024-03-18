<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Collector\Expr;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use SilverStripe\ORM\DataObject;

/**
 * @implements Collector<Expr, array{string, string, string, int}>
 */
final class MagicDataObjectCallCollector implements Collector
{
    #[Override]
    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @param Expr $node
     * @return ?array{string, string, string, int}
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!($node instanceof MethodCall || $node instanceof PropertyFetch)) {
            return null;
        }

        if ($scope->getFunction() === null) {
            return null;
        }

        if (!$node->name instanceof Identifier) {
            return null;
        }

        if (!$node->var instanceof MethodCall) {
            return null;
        }

        if (!$node->var->name instanceof Identifier) {
            return null;
        }

        $methodName = $node->var->name->toString();
        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return null;
        }

        if ($classReflection->hasNativeMethod($methodName)) {
            return null;
        }

        if (!$classReflection->hasMethod($methodName)) {
            return null;
        }

        if ((new ObjectType(DataObject::class))->isSuperTypeOf($scope->getType($node->var))->no()) {
            return null;
        }

        return [
            $classReflection->getName(),
            $scope->getFunction()->getName(),
            $node->name->toString(),
            $node->getLine(),
        ];
    }
}
