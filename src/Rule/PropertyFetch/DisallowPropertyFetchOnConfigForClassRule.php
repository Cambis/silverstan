<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\PropertyFetch;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<PropertyFetch>
 * @see \Cambis\Silverstan\Tests\Rule\PropertyFetch\DisallowPropertyFetchOnConfigForClassRuleTest
 */
final readonly class DisallowPropertyFetchOnConfigForClassRule implements SilverstanRuleInterface
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow property fetch on `SilverStripe\Core\Config\Config_ForClass`. ' .
            "PHPStan cannot resolve the type of the property, use `self::config()->get('property_name')` instead.",
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $singular_name = 'Foo';

    public function getType(): string
    {
        return self::config()->singular_name;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $singular_name = 'Foo';

    public function getType(): string
    {
        return self::config()->get('singular_name');
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

        if (!$node->var instanceof StaticCall) {
            return [];
        }

        if (!$node->var->class instanceof Name) {
            return [];
        }

        $type = $scope->getType($node->var);

        if ($type->isObject()->no()) {
            return [];
        }

        if ($type->isSuperTypeOf(new ObjectType('SilverStripe\Core\Config_ForClass'))->no()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    "Cannot resolve the type of %s::config()->%s. Use %s::config()->get('%s') instead.",
                    $scope->resolveName($node->var->class),
                    $node->name->name,
                    $scope->resolveName($node->var->class),
                    $node->name->name,
                )
            )
                ->identifier('silverstan.propertyFetch')
                ->build(),
        ];
    }
}
