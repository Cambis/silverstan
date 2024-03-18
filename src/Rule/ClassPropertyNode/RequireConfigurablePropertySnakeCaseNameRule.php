<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertyNode;

use Jawira\CaseConverter\Convert;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\ClassReflection;
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
 * @implements Rule<ClassPropertyNode>
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\RequireConfigurablePropertySnakeCaseNameRuleTest
 */
final class RequireConfigurablePropertySnakeCaseNameRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Configurable properties must be in snake_case.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $fooBar = 'foo bar';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class Foo extends \SilverStripe\ORM\DataObject
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
        if (!$this->isConfigurableProperty($node)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if ($this->shouldSkipClass($classReflection)) {
            return [];
        }

        if ((new Convert($node->getName()))->toSnake() === $node->getName()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Configurable property %s::$%s must be in snake_case format.',
                    $classReflection->getDisplayName(),
                    $node->getName(),
                )
            )
            ->identifier('silverstan.configurableProperty')
            ->build(),
        ];
    }

    private function isConfigurableProperty(ClassPropertyNode $property): bool
    {
        if (!$property->isPrivate()) {
            return false;
        }

        if (!$property->isStatic()) {
            return false;
        }

        return !str_contains((string) $property->getPhpDoc(), '@internal');
    }

    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return false;
        }

        return !$classReflection->hasTraitUse(Configurable::class);
    }
}
