<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\New_;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<New_>
 * @see \Cambis\Silverstan\Tests\Rule\New_\DisallowNewInstanceOnInjectableRuleTest
 */
final readonly class DisallowNewInstanceOnInjectableRule implements Rule
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private ReflectionProvider $reflectionProvider
    ) {
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
