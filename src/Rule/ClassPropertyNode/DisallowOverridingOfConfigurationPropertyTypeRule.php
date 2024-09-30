<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertyNode;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ParserNodeTypeToPHPStanType;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\VerbosityLevel;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<ClassPropertyNode>
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertyNode\DisallowOverridingOfConfigurationPropertyTypeRuleTest
 */
final readonly class DisallowOverridingOfConfigurationPropertyTypeRule implements SilverstanRuleInterface
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private PropertyReflectionAnalyser $propertyReflectionAnalyser,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Disallow overriding types of configuration properties.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App\Model;

class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo = 'foo';
}

final class Bar extends Foo
{
    private static string|bool $foo = false;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Model;

class Foo extends \SilverStripe\ORM\DataObject
{
    private static string $foo = 'foo';
}

final class Bar extends Foo
{
    private static string $foo = 'bar';
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
        if (!$this->propertyReflectionAnalyser->isConfigurationProperty($node)) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if (!$this->classReflectionAnalyser->isConfigurable($classReflection)) {
            return [];
        }

        $prototype = $this->reflectionResolver->resolveConfigurationPropertyReflection($classReflection->getParentClass(), $node->getName());

        if (!$prototype instanceof PropertyReflection) {
            return [];
        }

        $nativeType = ParserNodeTypeToPHPStanType::resolve($node->getNativeType(), $classReflection);
        $type = TypehintHelper::decideType($nativeType, $node->getPhpDocType());
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
