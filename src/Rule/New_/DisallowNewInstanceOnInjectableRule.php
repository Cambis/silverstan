<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\New_;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Core\Injector\Injectable;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function sprintf;

/**
 * @implements SilverstanRuleInterface<New_>
 * @see \Cambis\Silverstan\Tests\Rule\New_\DisallowNewInstanceOnInjectableRuleTest
 */
final readonly class DisallowNewInstanceOnInjectableRule implements SilverstanRuleInterface
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow instantiating a `\SilverStripe\Core\Injectable` class using `new`. Use create() instead.',
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

        if (!$classReflection->hasTraitUse(Injectable::class)) {
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
            ->build(),
        ];
    }
}
