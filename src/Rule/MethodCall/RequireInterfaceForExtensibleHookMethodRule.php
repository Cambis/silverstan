<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\MethodCall;

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
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use SilverStripe\Core\Extensible;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function count;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 * @see \Cambis\Silverstan\Tests\Rule\MethodCall\RequireInterfaceForExtensibleHookMethodRuleTest
 */
final class RequireInterfaceForExtensibleHookMethodRule implements Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
    public function __construct(
        private readonly FileTypeMapper $fileTypeMapper,
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Require extensible hook methods to be defined via an interface. Use the `@phpstan-silverstripe-extend` annotation resolve the interface location.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    /**
     * @phpstan-silverstripe-extend UpdateBar
     */
    public function bar(): string
    {
        $bar = 'bar';

        $this->extend('updateBar', $bar);

        return $bar;
    }
}

interface UpdateBar
{
    public function updateBar(string &$bar): void;
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

        if (!$classReflection->hasTraitUse(Extensible::class)) {
            return [];
        }

        if (!$node->name instanceof Identifier) {
            return [];
        }

        if ($node->name->toString() !== 'extend') {
            return [];
        }

        if ($node->isFirstClassCallable()) {
            return [];
        }

        // Let's try to get the first argument which should be the method name
        if (count($node->getArgs()) === 0) {
            return [];
        }

        $firstArg = $node->getArgs()[0];

        if (!$firstArg->value instanceof String_) {
            return [];
        }

        // Cool, we now have a method name to work with
        $extensibleHookMethodName = $firstArg->value->value;

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

        $extendAnnotationTagNodes = $this->getExtendAnnotationTagNodes($methodPhpDoc);

        if ($extendAnnotationTagNodes === []) {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Cannot find interface definition for extensible hook %s.',
                        $extensibleHookMethodName,
                    )
                )
                ->tip('Use the @phpstan-silverstripe-extend annotation to point an interface where the hook method is defined.')
                ->build(),
            ];
        }

        if (!$methodPhpDoc->getNullableNameScope() instanceof NameScope) {
            return [];
        }

        foreach ($extendAnnotationTagNodes as $annotationTagNode) {
            $value = $annotationTagNode->value;

            if (!$value instanceof GenericTagValueNode) {
                continue;
            }
    
            $name = $value->value;

            // Check that class was specified
            if ($name === '') {
                return [
                    RuleErrorBuilder::message(
                        sprintf('%s annotation does not specify anything.', $annotationTagNode->name)
                    )
                    ->build(),
                ];
            }

            $resolvedName = $methodPhpDoc->getNullableNameScope()->resolveStringName($name);

            if (!$this->reflectionProvider->hasClass($resolvedName)) {
                return [
                    RuleErrorBuilder::message('Could not resolve specified class.')->build(),
                ];
            }

            $extendReflection = $this->reflectionProvider->getClass($resolvedName);

            if (!$extendReflection->isInterface()) {
                return [
                    RuleErrorBuilder::message('Specified class must be an interface.')->build(),
                ];
            }

            if (!$extendReflection->hasNativeMethod($extensibleHookMethodName)) {
                continue;
            }

            // TODO: check that the method signatures match

            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    'Cannot find interface definition for extensible hook %s.',
                    $extensibleHookMethodName,
                )
            )
            ->tip('Use the @phpstan-silverstripe-extend annotation to point an interface where the hook method is defined.')
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
