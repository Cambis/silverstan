<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\PropertyFetch;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<PropertyFetch>
 * @see \Cambis\Silverstan\Tests\Rule\PropertyFetch\DisallowPropertyFetchOnConfigForClassRuleTest
 */
final readonly class DisallowPropertyFetchOnConfigForClassRule implements Rule
{
    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.configurationProperty.unresolvableType';

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
        $type = $scope->getType($node->var);

        // Ensure the resolved type has class reflections
        if ($type->getObjectClassReflections() === []) {
            return [];
        }

        // Resolved type must be `SilverStripe\Core\Config\Config_ForClass`
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if (!$classReflection->is('SilverStripe\Core\Config\Config_ForClass')) {
                return [];
            }
        }

        if (!$node->name instanceof Identifier) {
            return [];
        }

        if (!$node->var instanceof StaticCall && !$node->var instanceof MethodCall) {
            return [];
        }

        if ($node->var instanceof StaticCall) {
            $type = $node->var->class instanceof Name ? $scope->resolveTypeByName($node->var->class) : $scope->getType($node->var->class);
        }

        if ($node->var instanceof MethodCall) {
            $type = $scope->getType($node->var->var);
        }

        if ($type->getObjectClassNames() === []) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    "Cannot resolve the type of %s::config()->%s. Use %s::config()->get('%s') instead.",
                    $type->getObjectClassNames()[0],
                    $node->name->name,
                    $type->getObjectClassNames()[0],
                    $node->name->name,
                )
            )
                ->identifier(self::IDENTIFIER)
                ->build(),
        ];
    }
}
