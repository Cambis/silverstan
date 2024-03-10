<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Core\Extension;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @implements Rule<ClassMethod>
 *
 * @see \Cambis\Silverstan\Tests\Rule\ClassMethod\DisallowNonInterfacePublicMethodsOnExtensionRuleTest
 */
final class DisallowNonInterfacePublicMethodsOnExtensionRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow non interface public methods on `\SilverStripe\Core\Extension`, an interface should be used to define public methods added to an owner class.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class FooExtension extends \SilverStripe\Core\Extension
{
    public function foo(): string
    {
        return 'foo';
    }
}

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
interface FooExtensionInterface
{
    public function foo(): string;
}

final class FooExtension extends \SilverStripe\Core\Extension implements FooExtensionInterface
{
    public function foo(): string
    {
        return 'foo';
    }
}

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

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     */
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

        $parentClass = $classReflection->getParentClass();

        if (!$parentClass instanceof ClassReflection) {
            return false;
        }

        return $parentClass->hasNativeMethod($methodName);
    }
}
