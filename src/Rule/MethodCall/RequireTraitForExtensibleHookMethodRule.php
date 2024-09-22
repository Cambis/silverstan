<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\MethodCall;

use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Cambis\Silverstan\NodeAnalyser\CallLikeAnalyser;
use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\TypeComparator\CallLikeTypeComparator;
use Cambis\Silverstan\TypeResolver\CallLikeTypeResolver;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\NameScope;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function array_shift;
use function count;
use function sprintf;

/**
 * @implements SilverstanRuleInterface<MethodCall>
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\RequireTraitForExtensibleHookMethodRuleTest
 */
final readonly class RequireTraitForExtensibleHookMethodRule implements SilverstanRuleInterface
{
    public function __construct(
        private CallLikeAnalyser $callLikeAnalyser,
        private CallLikeTypeComparator $callLikeTypeComparator,
        private CallLikeTypeResolver $callLikeTypeResolver,
        private ClassAnalyser $classAnalyser,
        private FileTypeMapper $fileTypeMapper,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require extensible hook methods to be defined via a trait. ' .
            'This provides an easy way for developers to call these extension points. ' .
            'Trait methods must be `abstract protected`, return `void`, and having matching signatures. ' .
            'Use the `@phpstan-silverstripe-extend` annotation resolve the trait location. [STRICT]',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}

namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension
{
    protected function updateBar(string &$bar): void
    {
        $bar = 'foobar';
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Model;

final class Foo extends \SilverStripe\ORM\DataObject
{
    /**
     * @phpstan-silverstripe-extend \App\Concern\UpdateBar
     */
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}

namespace App\Concern;

trait UpdateBar
{
    abstract protected function updateBar(string &$bar): void;
}

namespace App\Extension;

/**
 * @extends \SilverStripe\Core\Extension<Foo & static>
 */
final class FooExtension extends \SilverStripe\Core\Extension
{
    use \App\Concern\UpdateBar;

    protected function updateBar(string &$bar): void
    {
        $bar = 'foobar';
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

    #[Override]
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return [];
        }

        if (!$this->classAnalyser->isExtensible($classReflection)) {
            return [];
        }

        if (!$node->name instanceof Identifier) {
            return [];
        }

        if ($node->name->toString() !== 'extend' && $node->name->toString() !== 'invokeWithExtensions') {
            return [];
        }

        if ($node->isFirstClassCallable()) {
            return [];
        }

        if ($node->getArgs() === []) {
            return [];
        }

        // Let's get the first argument which should be the method name
        $firstArg = $node->getArgs()[0];

        if (!$firstArg->value instanceof String_) {
            return [];
        }

        // Cool, we now have a method name to work with
        $hookMethodName = $firstArg->value->value;
        $functionReflection = $scope->getFunction();

        if ($functionReflection === null) {
            return [];
        }

        $methodPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            $scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
            $functionReflection->getName(),
            $functionReflection->getDocComment() ?? ''
        );

        // Find any `@phpstan-silverstripe-extend` annotations
        $extendAnnotationTagNodes = $this->getExtendAnnotationTagNodes($methodPhpDoc);

        if ($extendAnnotationTagNodes === []) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Cannot find trait definition for extensible hook %s().',
                        $hookMethodName,
                    )
                )
                    ->tip('Use the @phpstan-silverstripe-extend annotation to point an trait where the hook method is defined.')
                    ->identifier('silverstan.extensibleTraitMethod')
                    ->build(),
            ];
        }

        if (!$methodPhpDoc->getNullableNameScope() instanceof NameScope) {
            return [];
        }

        // Grab the parameter types, these will be used later
        $methodParametersTypes = $this->callLikeTypeResolver->resolveTypesFromArgs($node->getArgs(), $scope);

        // Remove the first parameter type as this is the method name
        array_shift($methodParametersTypes);

        foreach ($extendAnnotationTagNodes as $annotationTagNode) {
            $value = $annotationTagNode->value;

            if (!$value instanceof GenericTagValueNode) {
                continue;
            }

            $name = $value->value;

            // Check that a class was specified
            if ($name === '') {
                return [
                    RuleErrorBuilder::message(
                        sprintf('%s annotation does not specify anything.', $annotationTagNode->name)
                    )
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            // Check that we can resolve the specified class
            $resolvedName = $methodPhpDoc->getNullableNameScope()->resolveStringName($name);

            if (!$this->reflectionProvider->hasClass($resolvedName)) {
                return [
                    RuleErrorBuilder::message(sprintf('Could not resolve specified class %s.', $resolvedName))
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            $extendReflection = $this->reflectionProvider->getClass($resolvedName);

            // Check if class in a trait
            if (!$extendReflection->isTrait()) {
                return [
                    RuleErrorBuilder::message(sprintf('Specified class %s must be a trait.', $resolvedName))
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            // Skip if the class doesn't contain the method definition
            if (!$extendReflection->hasNativeMethod($hookMethodName)) {
                continue;
            }

            // Check that the class only has a single method definition
            if (count($extendReflection->getNativeReflection()->getMethods()) !== 1) {
                return [
                    RuleErrorBuilder::message(sprintf('Specified class %s can only contain a single method definition.', $resolvedName))
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            $hookMethodReflection = $extendReflection->getNativeReflection()->getMethod($hookMethodName);
            $hookMethodExtendedReflection = $extendReflection->getNativeMethod($hookMethodName);
            $hookMethodVariant = ParametersAcceptorSelector::selectSingle($hookMethodExtendedReflection->getVariants());

            // Check that the method is abstract
            if (!$hookMethodReflection->isAbstract()) {
                return [
                    RuleErrorBuilder::message(sprintf('Specified class method %s::%s() must be abstract.', $resolvedName, $hookMethodName))
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            // Check the the method is protected
            if (!$hookMethodReflection->isProtected()) {
                return [
                    RuleErrorBuilder::message(sprintf('Specified class method %s::%s() must be protected.', $resolvedName, $hookMethodName))
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            // Check that the method returns void
            if ($hookMethodVariant->getReturnType()->isVoid()->no()) {
                return [
                    RuleErrorBuilder::message(sprintf('Specified class method %s::%s() must return void.', $resolvedName, $hookMethodName))
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            // Check that literal values are passed by reference
            $hookParameters = $hookMethodVariant->getParameters();

            if (!$this->callLikeAnalyser->areLiteralParametersPassedByReference($hookParameters)) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('Specified class method %s::%s() literal parameters must be passed by reference.', $resolvedName, $hookMethodName)
                    )
                        ->tip('See: https://docs.silverstripe.org/en/5/developer_guides/extending/extensions/#modifying-existing-methods')
                        ->identifier('silverstan.extensibleTraitMethod')
                        ->build(),
                ];
            }

            // Check that the method signatures match
            $hookParametersTypes = $this->callLikeTypeResolver->resolveTypesFromParameters($hookParameters);

            if (!$this->callLikeTypeComparator->doSignaturesMatch($methodParametersTypes, $hookParametersTypes)) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('Specified class method %s::%s() signature does not match.', $resolvedName, $hookMethodName)
                    )->identifier('silverstan.extensibleTraitMethod')->build(),
                ];
            }

            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Cannot find trait definition for extensible hook %s.',
                    $hookMethodName,
                )
            )
                ->tip('Use the @phpstan-silverstripe-extend annotation to point an trait where the hook method is defined.')
                ->identifier('silverstan.extensibleTraitMethod')
                ->build(),
        ];
    }

    /**
     * @return PhpDocTagNode[]
     */
    private function getExtendAnnotationTagNodes(ResolvedPhpDocBlock $resolvedPhpDocBlock): array
    {
        $phpDocNodes = $resolvedPhpDocBlock->getPhpDocNodes();

        $annotations = [];

        foreach ($phpDocNodes as $docNode) {
            $annotations = [...$annotations, ...$docNode->getTagsByName('@phpstan-silverstripe-extend')];
            $annotations = [...$annotations, ...$docNode->getTagsByName('@psalm-silverstripe-extend')];
        }

        return $annotations;
    }
}
