<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\InClassNode;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ValueObject\RequiredProperties;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use SilverStripe\ORM\DataObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

use function sprintf;
use function str_contains;

/**
 * @implements SilverstanRuleInterface<InClassNode>
 * @see \Cambis\Silverstan\Tests\Rule\InClassNode\RequireConfigurationPropertyOverrideRuleTest
 */
final class RequireConfigurationPropertyOverrideRule implements SilverstanRuleInterface
{
    /**
     * @var RequiredProperties[]
     */
    private array $requiredProperties;

    /**
     * @param array<array{class: class-string, properties: array<string>}> $requiredProperties
     */
    public function __construct(array $requiredProperties = [])
    {
        foreach ($requiredProperties as $property) {
            Assert::keyExists($property, 'class');
            Assert::keyExists($property, 'properties');
            Assert::string($property['class']);
            Assert::allString($property['properties']);

            $this->requiredProperties[] = new RequiredProperties($property['class'], $property['properties']);
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
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                        'requiredProperties' => [
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
        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        $errors = [];

        foreach ($this->requiredProperties as $requiredProperty) {
            if (!$classReflection->isSubclassOf($requiredProperty->getClassName())) {
                continue;
            }

            foreach ($requiredProperty->getProperties() as $property) {
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
        }

        return $errors;
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
