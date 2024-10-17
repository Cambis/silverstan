<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Type;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use function in_array;

/**
 * This extension attempts to resolve the type of `SilverStripe\Core\Config\Config_ForClass::get()` by looking for a matching native property from the called class or one of its parents.
 *
 * @see \Cambis\Silverstan\Tests\Extension\Type\ConfigForClassGetReturnTypeExtensionTest
 */
final readonly class ConfigForClassGetReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'get',
        'uninherited',
    ];

    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private TypeResolver $configurationPropertyTypeResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getClass(): string
    {
        return 'SilverStripe\Core\Config\Config_ForClass';
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    #[Override]
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (!$methodCall->var instanceof StaticCall) {
            return null;
        }

        if (!$methodCall->var->class instanceof Name) {
            return null;
        }

        $className = $scope->resolveName($methodCall->var->class);

        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$this->classReflectionAnalyser->isConfigurable($classReflection)) {
            return null;
        }

        if ($methodCall->getArgs() === []) {
            return null;
        }

        // Let's get the first argument which should be the property name
        $firstArgValue = $methodCall->getArgs()[0]->value;

        if (!$firstArgValue instanceof String_) {
            return null;
        }

        // Cool, now we have a property name to work with
        $propertyName = $firstArgValue->value;

        return $this->configurationPropertyTypeResolver->resolveConfigurationPropertyType($classReflection, $propertyName);
    }
}
