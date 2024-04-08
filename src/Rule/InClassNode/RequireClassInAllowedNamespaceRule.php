<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\InClassNode;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ValueObject\ClassAllowedNamespace;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function array_reverse;
use function implode;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<InClassNode>
 * @see \Cambis\Silverstan\Tests\Rule\InClassNode\RequireClassInAllowedNamespaceRuleTest
 */
final class RequireClassInAllowedNamespaceRule implements SilverstanRuleInterface
{
    /**
     * @var ClassAllowedNamespace[]
     */
    private array $classAllowedNamespaces;

    /**
     * @param array<array{class: class-string, allowedNamespaces: array<string>}> $classes
     */
    public function __construct(
        array $classes
    ) {
        // Reverse the order so custom configuration takes precedence over default configuration
        foreach (array_reverse($classes) as $klass) {
            $this->classAllowedNamespaces[] = new ClassAllowedNamespace($klass['class'], $klass['allowedNamespaces']);
        }
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require a class to be in an allowed namespace.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App;

final class Foo extends \SilverStripe\ORM\DataObject
{
}

namespace App;

final class FooController extends \SilverStripe\Control\Controller
{
}

namespace App;

final class FooExtension extends \SilverStripe\Core\Extension
{
}

namespace App;

final class FooTask extends \SilverStripe\Dev\BuildTask
{
}

namespace App;

final class FooJob extends \Symbiote\QueuedJobs\Services\AbstractQueuedJob
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
}

namespace App\Controller;

final class FooController extends \SilverStripe\Control\Controller
{
}

namespace App\Extension;

final class FooExtension extends \SilverStripe\Core\Extension
{
}

namespace App\Task;

final class FooTask extends \SilverStripe\Dev\BuildTask
{
}

namespace App\Job;

final class FooJob extends \Symbiote\QueuedJobs\Services\AbstractQueuedJob
{
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'classes' => [
                            [
                                'class' => DataObject::class,
                                'allowedNamespaces' => ['Model'],
                            ],
                            [
                                'class' => Controller::class,
                                'allowedNamespaces' => ['Controller'],
                            ],
                            [
                                'class' => Extension::class,
                                'allowedNamespaces' => ['Extension'],
                            ],
                            [
                                'class' => BuildTask::class,
                                'allowedNamespaces' => ['Task'],
                            ],
                            [
                                'class' => 'Symbiote\\QueuedJobs\\Services\\AbstractQueuedJob',
                                'allowedNamespaces' => ['Job'],
                            ],
                        ],
                    ]
                )],
        );
    }

    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->getOriginalNode() instanceof Class_) {
            return [];
        }

        $classReflection = $node->getClassReflection();

        if ($classReflection->isAnonymous()) {
            return [];
        }

        $namespace = $scope->getNamespace();

        if ($namespace === null) {
            return [];
        }

        $classAllowedNamespace = $this->getClassAllowedNamespace($classReflection);

        if (!$classAllowedNamespace instanceof ClassAllowedNamespace) {
            return [];
        }

        if ($classAllowedNamespace->isNamespaceAllowed($namespace)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Class %s must be located in one of [%s] namespace.',
                    $classReflection->getDisplayName(),
                    implode(', ', $classAllowedNamespace->allowedNamespaces)
                )
            )->build(),
        ];
    }

    private function getClassAllowedNamespace(ClassReflection $classReflection): ?ClassAllowedNamespace
    {
        foreach ($this->classAllowedNamespaces as $classAllowedNamespace) {
            if (!$classReflection->isSubclassOf($classAllowedNamespace->className)) {
                continue;
            }

            return $classAllowedNamespace;
        }

        return null;
    }
}
