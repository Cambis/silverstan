<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\StaticPropertyFetch;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<StaticPropertyFetch>
 * @see \Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurationPropertyRuleTest
 */
final readonly class DisallowStaticPropertyFetchOnConfigurationPropertyRule implements Rule
{
    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.configurationProperty.unsafe';

    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private PropertyReflectionAnalyser $propertyReflectionAnalyser,
    ) {
    }

    #[Override]
    public function getNodeType(): string
    {
        return StaticPropertyFetch::class;
    }

    /**
     * @param StaticPropertyFetch $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof VarLikeIdentifier) {
            return [];
        }

        $type = null;

        if ($node->class instanceof Expr) {
            $type = $scope->getType($node->class);
        }

        if ($node->class instanceof Name) {
            $type = $scope->resolveTypeByName($node->class);
        }

        if ($type->getObjectClassReflections() === []) {
            return [];
        }

        $classReflection = $type->getObjectClassReflections()[0];

        if (!$this->classReflectionAnalyser->isConfigurable($classReflection)) {
            return [];
        }

        if ($type->hasStaticProperty($node->name->name)->no()) {
            return [];
        }

        $propertyReflection = $classReflection->getStaticProperty($node->name->name);

        if (!$this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection)) {
            return [];
        }

        $varName = $node->class instanceof Name ? $node->class->toString() : $classReflection->getName();

        return [
            RuleErrorBuilder::message(
                sprintf(
                    "Unsafe access to configuration property %s::$%s through %s::. Use %s::config->get('%s') instead.",
                    $classReflection->getName(),
                    $node->name->name,
                    $varName,
                    $varName,
                    $node->name->name
                )
            )
                ->identifier(self::IDENTIFIER)
                ->tip('See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties')
                ->build(),
        ];
    }
}
