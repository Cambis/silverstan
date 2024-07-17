<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Type;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\TypeResolver\ConfigurationPropertyTypeResolver;
use Override;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use function count;
use function in_array;

/**
 * This extension attempts to resolve the type of `\SilverStripe\Config\Collections\ConfigCollectionInterface::get()` by looking for a matching native property from the called class or one of its parents.
 *
 * @see \Cambis\Silverstan\Tests\Extension\Type\ConfigCollectionInterfaceGetReturnTypeExtensionTest
 */
final readonly class ConfigCollectionInterfaceGetReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'get',
    ];

    public function __construct(
        private ClassAnalyser $classAnalyser,
        private ConfigurationPropertyTypeResolver $configurationPropertyTypeResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getClass(): string
    {
        return ConfigCollectionInterface::class;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    #[Override]
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (count($methodCall->getArgs()) < 2) {
            return null;
        }

        // Let's get the first argument which should be class name
        $firstArgValue = $methodCall->getArgs()[0]->value;

        if (!$firstArgValue instanceof String_ && !$firstArgValue instanceof ClassConstFetch) {
            return null;
        }

        if ($firstArgValue instanceof String_) {
            $className = $firstArgValue->value;
        } elseif ($firstArgValue->class instanceof Name) {
            $className = $scope->resolveName($firstArgValue->class);
        } else {
            return null;
        }

        // Cool, we now have a class name to work with
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$this->classAnalyser->isConfigurable($classReflection)) {
            return null;
        }

        // Lets get the second argument which should be the property name
        $secondArgValue = $methodCall->getArgs()[1]->value;

        if (!$secondArgValue instanceof String_) {
            return null;
        }

        // Cool, now we have a property name to work with
        $propertyName = $secondArgValue->value;

        return $this->configurationPropertyTypeResolver->resolveConfigurationPropertyType($classReflection, $propertyName);
    }
}
