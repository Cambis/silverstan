<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\New_;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<New_>
 * @see \Cambis\Silverstan\Tests\Rule\New_\DisallowNewInstanceOnInjectableRuleTest
 */
final class DisallowNewInstanceOnInjectableRule implements SilverstanRuleInterface
{
    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow instantiating a `SilverStripe\Core\Injectable` class using `new`. Use create() instead.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo
{
    use \SilverStripe\Core\Injectable;
}

$foo = new Foo();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo
{
    use \SilverStripe\Core\Injectable;
}

$foo = Foo::create();
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
        return New_::class;
    }

    /**
     * @param New_ $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Name) {
            return [];
        }

        if (!$this->reflectionProvider->hasClass($node->class->toString())) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($node->class->toString());

        if (!$this->classReflectionAnalyser->isInjectable($classReflection)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Use %s::create() instead of new %s().',
                    $node->class->toString(),
                    $node->class->toString(),
                )
            )
                ->identifier('silverstan.newInjectable')
                ->build(),
        ];
    }
}
