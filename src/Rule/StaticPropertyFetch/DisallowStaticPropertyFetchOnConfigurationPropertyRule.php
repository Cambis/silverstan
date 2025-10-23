<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\StaticPropertyFetch;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<StaticPropertyFetch>
 * @see \Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurationPropertyRuleTest
 */
final class DisallowStaticPropertyFetchOnConfigurationPropertyRule implements SilverstanRuleInterface
{
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private PropertyReflectionAnalyser $propertyReflectionAnalyser;
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, PropertyReflectionAnalyser $propertyReflectionAnalyser)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->propertyReflectionAnalyser = $propertyReflectionAnalyser;
    }
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
        $type = null;
        if ($node->class instanceof Expr) {
            $type = $scope->getType($node->class);
        }
        if ($node->class instanceof Name) {
            $type = $scope->resolveTypeByName($node->class);
        }
        if ($type->getObjectClassReflections() === []) {
            return [];
        }
        $classReflection = $type->getObjectClassReflections()[0];
        if (!$this->classReflectionAnalyser->isConfigurable($classReflection)) {
            return [];
        }
        if ($type->hasProperty($node->name->name)->no()) {
            return [];
        }
        $propertyReflection = $classReflection->getProperty($node->name->name, $scope);
        if (!$this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection)) {
            return [];
        }
        $varName = $node->class instanceof Name ? $node->class->toString() : $classReflection->getName();
        return [
            RuleErrorBuilder::message(
                sprintf(
                    "Unsafe access to configuration property %s::$%s through %s::. Use %s::config()->get('%s') instead.",
                    $classReflection->getName(),
                    $node->name->name,
                    $varName,
                    $varName,
                    $node->name->name
                )
            )
                ->identifier('silverstan.configurationProperty')
                ->tip('See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties')
                ->build(),
        ];
    }
}
