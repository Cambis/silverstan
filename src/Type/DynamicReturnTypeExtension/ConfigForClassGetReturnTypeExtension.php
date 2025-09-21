<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\DynamicReturnTypeExtension;

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
 * @see \Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\ConfigForClassGetReturnTypeExtensionTest
 */
final class ConfigForClassGetReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private TypeResolver $configurationPropertyTypeResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'get',
        'uninherited',
    ];

    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, TypeResolver $configurationPropertyTypeResolver, ReflectionProvider $reflectionProvider)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->configurationPropertyTypeResolver = $configurationPropertyTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
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
        // Attempt to resolve the type of the var
        if (!$methodCall->var instanceof StaticCall && !$methodCall->var instanceof MethodCall) {
            return null;
        }

        if ($methodCall->var instanceof StaticCall) {
            $type = $methodCall->var->class instanceof Name ? $scope->resolveTypeByName($methodCall->var->class) : $scope->getType($methodCall->var->class);
        }

        if ($methodCall->var instanceof MethodCall) {
            $type = $scope->getType($methodCall->var->var);
        }

        if ($type->getObjectClassNames() === []) {
            return null;
        }

        $className = $type->getObjectClassNames()[0];

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
