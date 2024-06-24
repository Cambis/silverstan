<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassMethod;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Core\Extension;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @implements SilverstanRuleInterface<ClassMethod>
 *
 * @see \Cambis\Silverstan\Tests\Rule\ClassMethod\DisallowNonInterfacePublicMethodsOnExtensionRuleTest
 */
final class DisallowNonInterfacePublicMethodsOnExtensionRule implements SilverstanRuleInterface
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow non interface public methods on `\SilverStripe\Core\Extension`, an interface should be used to define public methods added to an owner class.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<\App\Model\Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension
{
    public function foo(): string
    {
        return 'foo';
    }
}

namespace App\Model;

/**
 * @mixin FooExtension
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): void
    {
        $this->foo(); // Visible
        $this->getOwner(); // Visible
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Contract;

interface FooExtensionInterface
{
    public function foo(): string;
}

namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<\App\Model\Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension implements FooExtensionInterface
{
    public function foo(): string
    {
        return 'foo';
    }
}

namespace App\Model;

/**
 * @mixin FooExtensionInterface
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): void
    {
        $this->foo(); // Visible
        $this->getOwner(); // Not visible
    }
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

        if ($this->shouldSkipClass($classReflection)) {
            return [];
        }

        if ($this->shouldSkipClassMethod($node, $classReflection)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Use explicit interface contract or a service to define public facing extension methods.'
            )
                ->build(),
        ];
    }

    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        return !$classReflection->isSubclassOf(Extension::class);
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod, ClassReflection $classReflection): bool
    {
        if (!$classMethod->isPublic()) {
            return true;
        }

        if ($classMethod->isStatic()) {
            return true;
        }

        return $this->isMethodRequiredByParent($classReflection, $classMethod->name->toString());
    }

    private function isMethodRequiredByParent(ClassReflection $classReflection, string $methodName): bool
    {
        $interfaces = $classReflection->getInterfaces();

        foreach ($interfaces as $interface) {
            if ($interface->hasNativeMethod($methodName)) {
                return true;
            }
        }

        $parents = $classReflection->getParents();

        foreach ($parents as $parent) {
            if ($parent->hasNativeMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
