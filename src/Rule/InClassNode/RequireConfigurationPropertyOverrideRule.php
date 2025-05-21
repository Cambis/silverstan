<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\InClassNode;

use Cambis\Silverstan\Normaliser\Normaliser;
use Cambis\Silverstan\ValueObject\ClassRequiredProperty;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_is_list;
use function array_key_exists;
use function array_reverse;
use function in_array;
use function sprintf;
use function str_contains;

/**
 * @phpstan-type ClassConfig array{class: class-string, properties: list<string>}
 * @implements Rule<InClassNode>
 *
 * @see \Cambis\Silverstan\Tests\Rule\InClassNode\RequireConfigurationPropertyOverrideRuleTest
 */
final class RequireConfigurationPropertyOverrideRule implements Rule
{
    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.configurationProperty.required';

    /**
     * @var string[][]
     */
    private const PROPERTY_ALLOWLIST = [
        'Page' => ['table_name'],
        'SilverStripe\CMS\Model\SiteTree' => ['table_name'],
    ];

    /**
     * @var ClassRequiredProperty[]
     */
    private array $classRequiredProperties;

    /**
     * @param list<ClassConfig>|array<class-string, list<string>> $classes
     */
    public function __construct(
        private readonly Normaliser $normaliser,
        array $classes = []
    ) {
        $classes = array_reverse($classes);
        $classRequiredProperties = [];

        if (!array_is_list($classes)) {
            /** @var array<class-string, list<string>> $classes */
            foreach ($classes as $className => $properties) {
                $classRequiredProperties[] = new ClassRequiredProperty(
                    $this->normaliser->normaliseNamespace($className),
                    $properties
                );
            }

            $this->classRequiredProperties = $classRequiredProperties;

            return;
        }

        /** @var list<ClassConfig> $classes */
        foreach ($classes as $classConfig) {
            $classRequiredProperties[] = new ClassRequiredProperty($classConfig['class'], $classConfig['properties']);
        }

        $this->classRequiredProperties = $classRequiredProperties;
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
            if (
                array_key_exists($classReflection->getName(), self::PROPERTY_ALLOWLIST) &&
                in_array($property, self::PROPERTY_ALLOWLIST[$classReflection->getName()], true)
            ) {
                continue;
            }

            if ($this->hasConfigurationProperty($classReflection, $property)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'Class %s is missing required configuration property $%s',
                    $classReflection->getDisplayName(),
                    $property
                )
            )
                ->identifier(self::IDENTIFIER)
                ->build();
        }

        return $errors;
    }

    private function getClassRequiredProperty(ClassReflection $classReflection): ?ClassRequiredProperty
    {
        foreach ($this->classRequiredProperties as $requiredProperty) {
            if (!$classReflection->is($requiredProperty->className)) {
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
