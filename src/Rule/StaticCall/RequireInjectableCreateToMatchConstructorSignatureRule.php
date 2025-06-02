<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\StaticCall;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use function sprintf;

/**
 * @implements Rule<StaticCall>
 *
 * @see \Cambis\Silverstan\Tests\Rule\StaticCall\RequireInjectableCreateToMatchConstructorSignatureRuleTest
 */
final readonly class RequireInjectableCreateToMatchConstructorSignatureRule implements Rule
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private FunctionCallParametersCheck $functionCallParametersCheck
    ) {
    }

    #[Override]
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        if ($node->name->toLowerString() !== 'create') {
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

        if (!$this->classReflectionAnalyser->isInjectable($classReflection)) {
            return [];
        }

        if (!$classReflection->hasConstructor()) {
            return [];
        }

        $constructorReflection = $classReflection->getConstructor();
        $methodName = sprintf('%s::create()', $classReflection->getDisplayName());

        // @phpstan-ignore phpstanApi.method
        return $this->functionCallParametersCheck->check(
            ParametersAcceptorSelector::selectFromArgs(
                $scope,
                $node->getArgs(),
                $constructorReflection->getVariants(),
                $constructorReflection->getNamedArgumentsVariants()
            ),
            $scope,
            $constructorReflection->getDeclaringClass()->isBuiltin(),
            $node,
            'staticMethod',
            $constructorReflection->acceptsNamedArguments(),
            'Method ' . $methodName . ' invoked with %d parameter, %d required.',
            'Method ' . $methodName . ' invoked with %d parameters, %d required.',
            'Method ' . $methodName . ' invoked with %d parameter, at least %d required.',
            'Method ' . $methodName . ' invoked with %d parameters, at least %d required.',
            'Method ' . $methodName . ' invoked with %d parameter, %d-%d required.',
            'Method ' . $methodName . ' invoked with %d parameters, %d-%d required.',
            '%s of method ' . $methodName . ' expects %s, %s given.',
            '', // no return type for constructor
            '%s of method ' . $methodName . ' is passed by reference, so it expects variables only.',
            'Unable to resolve the template type %s in call to method ' . $methodName,
            'Missing parameter $%s in call to method ' . $methodName . '.',
            'Unknown parameter $%s in call to method ' . $methodName . '.',
            'Return type of call to method ' . $methodName . ' contains unresolvable type.',
            '%s of method ' . $methodName . ' contains unresolvable type.',
            'Method ' . $methodName . " invoked with %s, but it's not allowed because of @no-named-arguments.",
        );
    }
}
