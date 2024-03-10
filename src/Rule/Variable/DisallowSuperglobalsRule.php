<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

use function in_array;
use function sprintf;

/**
 * @implements Rule<Variable>
 * @see \Cambis\Silverstan\Tests\Rule\Variable\DisallowSuperglobalsRuleTest
 */
final class DisallowSuperglobalsRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
    /**
     * @var string[]
     */
    private const SUPERGLOBALS = [
        '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV', 'GLOBALS',
    ];

    public function __construct(
        /** @var string[] */
        private readonly array $disallowedSuperglobals = self::SUPERGLOBALS
    ) {
        foreach ($disallowedSuperglobals as $disallowedSuperglobal) {
            Assert::inArray($disallowedSuperglobal, self::SUPERGLOBALS);
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow the use of superglobals ($_GET, $_REQUEST etc.).',
            [new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class CustomMiddleware implements \SilverStripe\Control\Middleware\HTTPMiddleware
{
    /**
     * @return void
     */
    public function process(\SilverStripe\Control\HTTPRequest $request, callable $delegate)
    {
        $foo =  $_GET['foo'];
    }
}
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
final class CustomMiddleware implements \SilverStripe\Control\Middleware\HTTPMiddleware
{
    /**
     * @return void
     */
    public function process(\SilverStripe\Control\HTTPRequest $request, callable $delegate)
    {
        $foo =  $request->getVar('foo');
    }
}
CODE_SAMPLE
                ,
                [
                    'enabled' => true,
                    'disallowedSuperglobals' => self::SUPERGLOBALS,
                ]
            ),
            ]
        );
    }

    public function getNodeType(): string
    {
        return Variable::class;
    }

    /**
     * @param Variable $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $functionReflection = $scope->getFunction();

        if ($this->shouldSkipFunctionReflection($functionReflection)) {
            return [];
        }

        if (!in_array($node->name, $this->disallowedSuperglobals, true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'You should not directly access the $%s superglobal. Consider using an alternative.',
                    $node->name,
                )
            )
            ->build(),
        ];
    }


    /**
     * @param FunctionReflection|MethodReflection|null $reflection
     */
    private function shouldSkipFunctionReflection($reflection): bool
    {
        if ($reflection instanceof FunctionReflection) {
            return false;
        }

        return !$reflection instanceof MethodReflection;
    }
}
