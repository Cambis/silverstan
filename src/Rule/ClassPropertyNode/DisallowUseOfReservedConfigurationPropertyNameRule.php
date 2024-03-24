<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertyNode;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function sprintf;
use function str_contains;

/**
 * @implements Rule<ClassPropertyNode>
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\DisallowUseOfReservedConfigurationPropertyNameRuleTest
 */
final class DisallowUseOfReservedConfigurationPropertyNameRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
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
        if ($this->isConfigurationProperty($node)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if ($this->shouldSkipClass($classReflection)) {
            return [];
        }

        $prototype = $this->findPrototype($classReflection, $node->getName());

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

    private function isConfigurationProperty(ClassPropertyNode|PhpPropertyReflection $property): bool
    {
        if (!$property->isPrivate()) {
            return false;
        }

        if (!$property->isStatic()) {
            return false;
        }

        if ($property instanceof ClassPropertyNode) {
            return !str_contains((string) $property->getPhpDoc(), '@internal');
        }

        return !str_contains((string) $property->getDocComment(), '@internal');
    }


    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return false;
        }

        return !$classReflection->hasTraitUse(Configurable::class);
    }

    private function findPrototype(ClassReflection $classReflection, string $propertyName): ?PhpPropertyReflection
    {
        foreach ($classReflection->getParents() as $parent) {
            if (!$parent->hasNativeProperty($propertyName)) {
                continue;
            }

            $property = $parent->getNativeProperty($propertyName);

            if (!$this->isConfigurationProperty($property)) {
                continue;
            }

            return $property;
        }

        return null;
    }
}
