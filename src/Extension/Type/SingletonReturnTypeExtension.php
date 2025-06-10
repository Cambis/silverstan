<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Type;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Normaliser\Normaliser;
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
     * @readonly
     */
    private ConfigurationResolver $configurationResolver;
    /**
     * @readonly
     */
    private Normaliser $normaliser;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string[]
     */
    private const SUPPORTED_FUNCTIONS = [
        'singleton',
    ];

    public function __construct(ConfigurationResolver $configurationResolver, Normaliser $normaliser, ReflectionProvider $reflectionProvider)
    {
        $this->configurationResolver = $configurationResolver;
        $this->normaliser = $normaliser;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), self::SUPPORTED_FUNCTIONS, true);
    }

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
        $serviceName = $this->normaliser->normaliseDotNotation($serviceName);
        $serviceName = $this->configurationResolver->resolveClassName($serviceName);
        if (!$this->reflectionProvider->hasClass($serviceName)) {
            return null;
        }
        return new ObjectType($serviceName);
    }
}
