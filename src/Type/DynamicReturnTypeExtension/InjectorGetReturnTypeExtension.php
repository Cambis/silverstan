<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\DynamicReturnTypeExtension;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Normaliser\Normaliser;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function in_array;

/**
 * This extension resolves the return type of `SilverStripe\Core\Injector\Injector::get()`.
 *
 * @see \Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\InjectorGetReturnTypeExtensionTest
 */
final class InjectorGetReturnTypeExtension implements DynamicMethodReturnTypeExtension
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
    private const SUPPORTED_METHODS = [
        'create',
        'createWithArgs',
        'get',
    ];

    public function __construct(ConfigurationResolver $configurationResolver, Normaliser $normaliser, ReflectionProvider $reflectionProvider)
    {
        $this->configurationResolver = $configurationResolver;
        $this->normaliser = $normaliser;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getClass(): string
    {
        return 'SilverStripe\Core\Injector\Injector';
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $serviceNameType = $scope->getType($methodCall->getArgs()[0]->value);
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
