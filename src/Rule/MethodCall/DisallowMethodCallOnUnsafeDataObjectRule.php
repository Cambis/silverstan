<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\MethodCall;

use Cambis\Silverstan\Normaliser\Normaliser;
use Cambis\Silverstan\Type\ObjectType\UnsafeObjectType;
use Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectDeleteTypeSpecifyingExtension;
use Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectExistsTypeSpecifyingExtension;
use Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectWriteTypeSpecifyingExtension;
use Cambis\Silverstan\ValueObject\ClassAllowedMethodCall;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_map;
use function in_array;
use function property_exists;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 *
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRuleTest
 */
final class DisallowMethodCallOnUnsafeDataObjectRule implements Rule
{
    /**
     * @readonly
     */
    private Normaliser $normaliser;
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
    private const IDENTIFIER = 'silverstan.dataObject.unsafe';

    /**
     * @var list<ClassAllowedMethodCall>
     * @readonly
     */
    private array $classAllowedMethodCalls;

    /**
     * @param array<class-string, list<string>> $allowedMethodCalls
     */
    public function __construct(
        Normaliser $normaliser,
        array $allowedMethodCalls = []
    ) {
        $this->normaliser = $normaliser;
        $classAllowedMethodCalls = [new ClassAllowedMethodCall('SilverStripe\ORM\DataObject', self::DEFAULT_ALLOWED_METHODS_CALLS)];

        foreach ($allowedMethodCalls as $className => $methodCalls) {
            // Normalise calls, remove brackets etc
            $normalisedMethodCalls = array_map(function (string $methodCall): string {
                return $this->normaliser->normaliseBracketNotation($methodCall);
            }, $methodCalls);

            $classAllowedMethodCalls[] = new ClassAllowedMethodCall($this->normaliser->normaliseNamespace($className), $normalisedMethodCalls);
        }

        $this->classAllowedMethodCalls = $classAllowedMethodCalls;
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

        // if (in_array($node->name->toString(), self::DEFAULT_ALLOWED_METHODS_CALLS, true)) {
        //     return [];
        // }

        // if (in_array($node->name->toString(), $this->allowedMethodCalls, true)) {
        //     return [];
        // }

        if (!$node->var instanceof MethodCall) {
            return [];
        }

        if ($node->var->name instanceof Expr) {
            return [];
        }

        // Type of the method owner
        $type = $scope->getType($node->var);

        // Only interested in DataObjects
        if (!$type instanceof UnsafeObjectType) {
            return [];
        }

        $ownerType = $scope->getType($node->var->var);

        foreach ($ownerType->getObjectClassReflections() as $classReflection) {
            // Skip any native methods, we're only interested in magic ones
            if ($classReflection->hasNativeMethod($node->var->name->toString())) {
                return [];
            }
        }

        foreach ($type->getObjectClassReflections() as $classReflection) {
            $classAllowedMethodCall = $this->getClassAllowedMethodCall($classReflection, $node->name->toString());

            // Check that the method call is allowed via configuration
            if ($classAllowedMethodCall instanceof ClassAllowedMethodCall) {
                return [];
            }
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

    private function getClassAllowedMethodCall(ClassReflection $classReflection, string $methodName): ?ClassAllowedMethodCall
    {
        foreach ($this->classAllowedMethodCalls as $classAllowedMethodCall) {
            if (!$classReflection->is($classAllowedMethodCall->className)) {
                continue;
            }

            if (!in_array($methodName, $classAllowedMethodCall->methodNames, true)) {
                continue;
            }

            return $classAllowedMethodCall;
        }

        return null;
    }
}
