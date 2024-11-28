<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Type;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Override;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function in_array;

/**
 * @see \Cambis\Silverstan\Tests\Type\InjectorGetReturnTypeExtensionTest
 */
final class SingletonReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_FUNCTIONS = [
        'singleton',
    ];

    public function __construct(
        private readonly ConfigurationResolver $configurationResolver,
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), self::SUPPORTED_FUNCTIONS, true);
    }

    #[Override]
    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $serviceNameType = $scope->getType($functionCall->getArgs()[0]->value);

        if ($serviceNameType->isString()->no()) {
            return null;
        }

        if ($serviceNameType->getConstantStrings() === []) {
            return null;
        }

        $serviceName = $serviceNameType->getConstantStrings()[0]->getValue();
        $serviceName = $this->configurationResolver->resolveDotNotation($serviceName);
        $serviceName = $this->configurationResolver->resolveClassName($serviceName);

        if (!$this->reflectionProvider->hasClass($serviceName)) {
            return null;
        }

        return new ObjectType($serviceName);
    }
}
