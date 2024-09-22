<?php

namespace Cambis\Silverstan\Rule\Interface_;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Nette\Utils\Strings;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function implode;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<Interface_>
 * @see \Cambis\Silverstan\Tests\Rule\Interface_\RequireInterfaceInAllowedNamespaceRuleTest
 */
final readonly class RequireInterfaceInAllowedNamespaceRule implements SilverstanRuleInterface
{
    /**
     * @var string
     */
    private const ALLOWED_NAMESPACE_REGEX = '#\b%s\b#';

    /**
     * @var string[]
     */
    private const DEFAULT_ALLOWED_NAMESPACES = ['Contract'];

    /**
     * @var string[]
     */
    private array $allowedNamespaces;

    /**
     * @param string[] $allowedNamespaces
     */
    public function __construct(
        array $allowedNamespaces
    ) {
        $this->allowedNamespaces = $allowedNamespaces === [] ? self::DEFAULT_ALLOWED_NAMESPACES : $allowedNamespaces;
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require an interface to be in an allowed namespace. [STRICT]',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App;

interface FooInterface
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Contract;

interface FooInterface
{
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'allowedNamespaces' => ['Contract'],
                    ]
                )],
        );
    }

    #[Override]
    public function getNodeType(): string
    {
        return Interface_::class;
    }

    /**
     * @param Interface_ $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $namespace = $scope->getNamespace();

        if ($namespace === null) {
            return [];
        }

        if ($this->isNamespaceAllowed($namespace)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Interface %s must be located in one of [%s] namespace.',
                    (string) $node->namespacedName,
                    implode(', ', $this->allowedNamespaces)
                )
            )->identifier('silverstan.allowedNamespace')->build(),
        ];
    }

    private function isNamespaceAllowed(string $namespace): bool
    {
        foreach ($this->allowedNamespaces as $allowedNamespace) {
            if (Strings::match($namespace, sprintf(self::ALLOWED_NAMESPACE_REGEX, $allowedNamespace)) !== null) {
                return true;
            }
        }

        return false;
    }
}
