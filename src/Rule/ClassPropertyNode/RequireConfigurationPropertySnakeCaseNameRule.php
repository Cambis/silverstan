<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertyNode;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use Jawira\CaseConverter\Convert;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<ClassPropertyNode>
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\RequireConfigurationPropertySnakeCaseNameRuleTest
 */
final readonly class RequireConfigurationPropertySnakeCaseNameRule implements SilverstanRuleInterface
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private PropertyAnalyser $propertyAnalyser
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Configuration properties must be in snake_case.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $fooBar = 'foo bar';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo_bar = 'foo bar';
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
        return ClassPropertyNode::class;
    }

    /**
     * @param ClassPropertyNode $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->propertyAnalyser->isConfigurationProperty($node)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if (!$this->classAnalyser->isConfigurable($classReflection)) {
            return [];
        }

        if ((new Convert($node->getName()))->toSnake() === $node->getName()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Configuration property %s::$%s must be in snake_case format.',
                    $classReflection->getDisplayName(),
                    $node->getName(),
                )
            )
                ->identifier('silverstan.configurationProperty')
                ->build(),
        ];
    }
}
