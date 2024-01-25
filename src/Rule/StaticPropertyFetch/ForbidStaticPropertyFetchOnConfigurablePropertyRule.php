<?php

namespace Cambis\Silverstan\Rule\StaticPropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function sprintf;
use function str_contains;

/**
 * @implements Rule<StaticPropertyFetch>
 * @see \Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\ForbidStaticPropertyFetchOnConfigurablePropertyRuleTest
 */
final class ForbidStaticPropertyFetchOnConfigurablePropertyRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Forbid static property fetch on configurable properties.',
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
        return $this->config()->get('singular_name');
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

    public function getNodeType(): string
    {
        return StaticPropertyFetch::class;
    }

    /**
     * @param StaticPropertyFetch $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof VarLikeIdentifier) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if ($this->shouldSkipClass($classReflection)) {
            return [];
        }

        $propertyReflection = $classReflection->getProperty($node->name->name, $scope);

        if ($this->shouldSkipProperty($propertyReflection)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Unsafe access to configurable property %s::$%s through self::.',
                    $classReflection->getName(),
                    $node->name->name,
                )
            )
            ->identifier('silverstan.configurableProperty')
            ->tip('See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties')
            ->build(),
        ];
    }

    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return false;
        }

        return !$classReflection->hasTraitUse(Configurable::class);
    }

    private function shouldSkipProperty(PropertyReflection $propertyReflection): bool
    {
        if (!$propertyReflection->isPrivate()) {
            return true;
        }

        if (!$propertyReflection->isStatic()) {
            return true;
        }

        return str_contains((string) $propertyReflection->getDocComment(), '@internal');
    }
}
