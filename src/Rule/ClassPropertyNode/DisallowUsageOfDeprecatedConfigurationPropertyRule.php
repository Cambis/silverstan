<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertyNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function rtrim;
use function sprintf;

/**
 * @implements Rule<ClassPropertyNode>
 *
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\DisallowUseOfDeprecatedConfigurationPropertyRuleTest
 */
final readonly class DisallowUsageOfDeprecatedConfigurationPropertyRule implements Rule
{
    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.configurationProperty.isDeprecated';

    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private PropertyReflectionAnalyser $propertyReflectionAnalyser,
        private ReflectionResolver $reflectionResolver
    ) {
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
        if (!$this->propertyReflectionAnalyser->isConfigurationProperty($node)) {
            return [];
        }

        $classReflection = $node->getClassReflection();

        if (!$classReflection->hasNativeProperty($node->getName())) {
            return [];
        }

        if (!$this->classReflectionAnalyser->isConfigurable($classReflection)) {
            return [];
        }

        $prototype = $this->reflectionResolver->resolveConfigurationPropertyReflection($classReflection->getParentClass(), $node->getName());

        if (!$prototype instanceof PropertyReflection) {
            return [];
        }

        if ($prototype->isDeprecated()->no()) {
            return [];
        }

        $message = sprintf(
            'Access to deprecated configuration property $%s of class %s.',
            $node->getName(),
            $prototype->getDeclaringClass()->getName(),
        );

        $description = $prototype->getDeprecatedDescription();

        if ($description !== null) {
            $message = sprintf("%s:\n%s.", rtrim($message, '.'), rtrim($description, '.'));
        }

        return [
            RuleErrorBuilder::message($message)->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
