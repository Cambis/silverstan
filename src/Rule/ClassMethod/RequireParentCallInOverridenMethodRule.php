<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassMethod;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ValueObject\ClassParentMethodCall;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function sprintf;

/**
 * @implements SilverstanRuleInterface<ClassMethod>
 *
 * @see \Cambis\Silverstan\Tests\Rule\ClassMethod\RequireParentCallInOverridenMethodRuleTest
 */
final class RequireParentCallInOverridenMethodRule implements SilverstanRuleInterface
{
    /**
     * @var ClassParentMethodCall[]
     */
    private array $classParentMethodCalls;

    /**
     * @param array<array{class: class-string, method: string, isFirst?: bool}> $classes
     */
    public function __construct(
        private readonly NodeFinder $nodeFinder,
        array $classes
    ) {
        foreach ($classes as $classParentCall) {
            $this->classParentMethodCalls[] = new ClassParentMethodCall(
                $classParentCall['class'],
                $classParentCall['method'],
                $classParentCall['isFirst'] ?? false
            );
        }
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require parent call in an overriden method.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        // Custom code...
    }

    protected function onAfterWrite(): void
    {
        // Custom code...
    }

    public function requireDefaultRecords(): void
    {
        // Custom code...
    }
}

namespace App\Tests\Model;

final class FooTest extends \SilverStripe\Dev\SapphireTest
{
    protected function setUp(): void
    {
        // Custom code...
    }

    protected function setUpBeforeClass(): void
    {
        // Custom code...
    }

    protected function tearDown(): void
    {
        // Custom code...
    }

    protected function tearDownAfterClass(): void
    {
        // Custom code...
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    protected function onBeforeWrite(): void
    {
        // Custom code...

        parent::onBeforeWrite();
    }

    protected function onAfterWrite(): void
    {
        // Custom code...

        parent::onAfterWrite();
    }

    public function requireDefaultRecords(): void
    {
        // Custom code...

        parent::requireDefaultRecords();
    }
}

namespace App\Tests\Model;

final class FooTest extends \SilverStripe\Dev\SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // Custom code...
    }

    protected function setUpBeforeClass(): void
    {
        parent::setupBeforeClass();

        // Custom code...
    }

    protected function tearDown(): void
    {
        // Custom code...

        parent::tearDown();
    }

    protected function tearDownAfterClass(): void
    {
        // Custom code...

        parent::tearDownAfterClass();
    }
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'classes' => [
                            [
                                'class' => DataObject::class,
                                'method' => 'onBeforeWrite',
                            ],
                            [
                                'class' => DataObject::class,
                                'method' => 'onAfterWrite',
                            ],
                            [
                                'class' => DataObject::class,
                                'method' => 'requireDefaultRecords',
                            ],
                            [
                                'class' => SapphireTest::class,
                                'method' => 'setUp',
                                'isFirst' => true,
                            ],
                            [
                                'class' => SapphireTest::class,
                                'method' => 'setUpBeforeClass',
                                'isFirst' => true,
                            ],
                            [
                                'class' => SapphireTest::class,
                                'method' => 'tearDown',
                            ],
                            [
                                'class' => SapphireTest::class,
                                'method' => 'tearDownAfterClass',
                            ],
                        ],
                    ]
                )],
        );
    }

    #[Override]
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        $classParentMethodCall = $this->getClassParentMethodCall($node, $classReflection);

        if (!$classParentMethodCall instanceof ClassParentMethodCall) {
            return [];
        }

        // Get all nodes that aren't an expression
        $nodes = $this->nodeFinder->find((array) $node->stmts, static function (Node $node): bool {
            return !$node instanceof Expression;
        });

        // If there are no calls, return an error
        if ($nodes === []) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() is missing required call to parent::%s().',
                        $classReflection->getDisplayName(),
                        $classParentMethodCall->getMethodName(),
                        $classParentMethodCall->getMethodName()
                    )
                )
                ->build(),
            ];
        }

        // Check if we have the required call
        if (!$this->hasClassParentMethodCall($nodes, $classParentMethodCall)) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() is missing required call to parent::%s().',
                        $classReflection->getDisplayName(),
                        $classParentMethodCall->getMethodName(),
                        $classParentMethodCall->getMethodName()
                    )
                )
                ->build(),
            ];
        }
       
        // Extra condition if the parent call should come first
        if (!$classParentMethodCall->getIsFirstCall()) {
            return [];
        }

        $firstCall = $nodes[0];

        if (
            !$firstCall instanceof StaticCall ||
            ($firstCall->name instanceof Identifier && $firstCall->name->toString() !== $classParentMethodCall->getMethodName())
        ) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Class method %s::%s() should call parent::%s() first.',
                        $classReflection->getDisplayName(),
                        $classParentMethodCall->getMethodName(),
                        $classParentMethodCall->getMethodName()
                    )
                )
                ->build(),
            ];
        }

        return [];
    }

    private function getClassParentMethodCall(ClassMethod $classMethod, ClassReflection $classReflection): ?ClassParentMethodCall
    {
        foreach ($this->classParentMethodCalls as $requiredParentCall) {
            if (!$classReflection->isSubclassOf($requiredParentCall->getClassName())) {
                continue;
            }

            if ($requiredParentCall->getMethodName() !== $classMethod->name->toString()) {
                continue;
            }

            return $requiredParentCall;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     */
    private function hasClassParentMethodCall(array $nodes, ClassParentMethodCall $requiredParentCall): bool
    {
        foreach ($nodes as $node) {
            if (!$node instanceof StaticCall) {
                continue;
            }

            if (!$node->class instanceof Name) {
                continue;
            }

            if (!$node->name instanceof Identifier) {
                continue;
            }

            if ($node->class->toString() !== 'parent') {
                continue;
            }

            if ($node->name->toString() !== $requiredParentCall->getMethodName()) {
                continue;
            }

            return true;
        }

        return false;
    }
}
