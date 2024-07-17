<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertyNode;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use Cambis\Silverstan\Reflection\ReflectionResolver;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<ClassPropertyNode>
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\DisallowUseOfReservedConfigurationPropertyNameRuleTest
 */
final readonly class DisallowUseOfReservedConfigurationPropertyNameRule implements SilverstanRuleInterface
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private PropertyAnalyser $propertyAnalyser,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow declaring a non configuration property that shares the same name with an existing configuration property.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    public static string $table_name = 'Foo';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $table_name = 'Foo';
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
        if ($this->propertyAnalyser->isConfigurationProperty($node)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if (!$this->classAnalyser->isConfigurable($classReflection)) {
            return [];
        }

        $prototype = $this->reflectionResolver->resolveConfigurationProperty($classReflection->getParentClass(), $node->getName());

        if (!$prototype instanceof PhpPropertyReflection) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'The name of non configuration property %s::$%s is already used by the configuration property %s::$%s.',
                    $classReflection->getDisplayName(),
                    $node->getName(),
                    $prototype->getDeclaringClass()->getDisplayName(),
                    $node->getName()
                )
            )
                ->tip(
                    'Did you mean to declare the property as `private static` instead?'
                )
                ->identifier('silverstan.configurationProperty')
                ->build(),
        ];
    }
}
