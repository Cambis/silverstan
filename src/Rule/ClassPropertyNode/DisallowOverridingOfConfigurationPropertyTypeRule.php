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
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<ClassPropertyNode>
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\DisallowOverridingOfConfigurationPropertyTypeRuleTest
 */
final readonly class DisallowOverridingOfConfigurationPropertyTypeRule implements Rule
{
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

        $nativeType = $classReflection->getNativeProperty($node->getName())->getReadableType();
        $type = $node->getPhpDocType() ?? $nativeType;

        $prototypeType = $prototype->getReadableType();

        if ($prototypeType->accepts($type, true)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Type %s of configuration property %s::$%s is not the same as type %s of overridden configuration property %s::$%s.',
                    $type->describe(VerbosityLevel::typeOnly()),
                    $classReflection->getDisplayName(),
                    $node->getName(),
                    $prototypeType->describe(VerbosityLevel::typeOnly()),
                    $prototype->getDeclaringClass()->getDisplayName(),
                    $node->getName()
                )
            )
                ->identifier('silverstan.configurationProperty')
                ->build(),
        ];
    }
}
