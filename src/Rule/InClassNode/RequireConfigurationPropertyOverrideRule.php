<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\InClassNode;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ValueObject\ClassRequiredProperty;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\ORM\DataObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function array_reverse;
use function sprintf;
use function str_contains;

/**
 * @implements SilverstanRuleInterface<InClassNode>
 * @see \Cambis\Silverstan\Tests\Rule\InClassNode\RequireConfigurationPropertyOverrideRuleTest
 */
final class RequireConfigurationPropertyOverrideRule implements SilverstanRuleInterface
{
    /**
     * @var ClassRequiredProperty[]
     */
    private array $classRequiredProperties;

    /**
     * @param array<array{class: class-string, properties: string[]}> $classes
     */
    public function __construct(array $classes = [])
    {
        // Reverse so custom configuration takes precedence over default configuration
        foreach (array_reverse($classes) as $klass) {
            $this->classRequiredProperties[] = new ClassRequiredProperty($klass['class'], $klass['properties']);
        }
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require a class to override a set of configuration properties.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $table_name = 'Foo';

    private static string $singlular_name = 'Foo';

    private static string $plural_name = 'Foos';
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'classes' => [
                            [
                                'class' => DataObject::class,
                                'properties' => ['table_name'],
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

        $classRequiredProperty = $this->getClassRequiredProperty($classReflection);

        if (!$classRequiredProperty instanceof ClassRequiredProperty) {
            return [];
        }

        $errors = [];

        foreach ($classRequiredProperty->properties as $property) {
            if ($this->hasConfigurationProperty($classReflection, $property)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'Class %s is missing configuration property $%s',
                    $classReflection->getDisplayName(),
                    $property
                )
            )
                ->identifier('silverstan.configurationProperty')
                ->build();
        }

        return $errors;
    }

    private function getClassRequiredProperty(ClassReflection $classReflection): ?ClassRequiredProperty
    {
        foreach ($this->classRequiredProperties as $requiredProperty) {
            if (!$classReflection->isSubclassOf($requiredProperty->className)) {
                continue;
            }

            return $requiredProperty;
        }

        return null;
    }

    private function hasConfigurationProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (!$classReflection->hasNativeProperty($propertyName)) {
            return false;
        }

        $property = $classReflection->getNativeProperty($propertyName);

        if (!$property->isPrivate()) {
            return false;
        }

        if (!$property->isStatic()) {
            return false;
        }

        return !str_contains((string) $property->getDocComment(), '@internal');
    }
}
