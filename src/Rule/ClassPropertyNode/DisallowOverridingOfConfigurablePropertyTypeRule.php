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
use PHPStan\Type\ParserNodeTypeToPHPStanType;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\VerbosityLevel;
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
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\DisallowOverridingOfConfigurablePropertyTypeRuleTest
 */
final class DisallowOverridingOfConfigurablePropertyTypeRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow overriding types of configurable properties.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo = 'foo';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Bar extends Foo
{
    private static string|bool $foo = false;
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
        if (!$this->isConfigurableProperty($node)) {
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

        $nativeType = ParserNodeTypeToPHPStanType::resolve($node->getNativeType(), $classReflection);
        $type = TypehintHelper::decideType($nativeType, $node->getPhpDocType());
        $prototypeType = TypehintHelper::decideType($prototype->getNativeType(), $prototype->getPhpDocType());

        if ($prototypeType->accepts($type, true)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Type %s of configurable property %s::$%s is not the same as type %s of overridden configurable property %s::$%s.',
                    $type->describe(VerbosityLevel::typeOnly()),
                    $classReflection->getDisplayName(),
                    $node->getName(),
                    $prototypeType->describe(VerbosityLevel::typeOnly()),
                    $prototype->getDeclaringClass()->getDisplayName(),
                    $node->getName()
                )
            )
            ->identifier('silverstan.configurableProperty')
            ->build(),
        ];
    }

    private function isConfigurableProperty(ClassPropertyNode|PhpPropertyReflection $property): bool
    {
        if (!$property->isPrivate()) {
            return false;
        }

        if (!$property->isStatic()) {
            return false;
        }

        if ($property instanceof ClassPropertyNode) {
            !str_contains((string) $property->getPhpDoc(), '@internal');
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

            if (!$this->isConfigurableProperty($property)) {
                continue;
            }

            return $property;
        }

        return null;
    }
}
