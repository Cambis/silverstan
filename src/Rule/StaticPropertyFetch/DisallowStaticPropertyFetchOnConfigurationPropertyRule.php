<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\StaticPropertyFetch;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<StaticPropertyFetch>
 * @see \Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurationPropertyRuleTest
 */
final readonly class DisallowStaticPropertyFetchOnConfigurationPropertyRule implements SilverstanRuleInterface
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private PropertyAnalyser $propertyAnalyser,
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow static property fetch on configuration properties.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $singular_name = 'Foo';

    public function getType(): string
    {
        return self::$singular_name;
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
        return StaticPropertyFetch::class;
    }

    /**
     * @param StaticPropertyFetch $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof VarLikeIdentifier) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if (!$this->classAnalyser->isConfigurable($classReflection)) {
            return [];
        }

        $propertyReflection = $classReflection->getProperty($node->name->name, $scope);

        if (!$this->propertyAnalyser->isConfigurationProperty($propertyReflection)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    "Unsafe access to configuration property %s::$%s through self::. Use self::config->get('%s') instead.",
                    $classReflection->getName(),
                    $node->name->name,
                    $node->name->name
                )
            )
                ->identifier('silverstan.configurationProperty')
                ->tip('See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties')
                ->build(),
        ];
    }
}
