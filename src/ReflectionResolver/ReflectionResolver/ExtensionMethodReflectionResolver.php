<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionMethod;
use function array_unique;
use function is_array;
use function strtolower;

final class ExtensionMethodReflectionResolver implements MethodReflectionResolverInterface
{
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private ConfigurationResolver $configurationResolver;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, ConfigurationResolver $configurationResolver, ReflectionProvider $reflectionProvider)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->configurationResolver = $configurationResolver;
        $this->reflectionProvider = $reflectionProvider;
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'extensions';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
            return [];
        }

        if (!$this->reflectionProvider->hasClass('SilverStripe\Core\Extension')) {
            return [];
        }

        $methodReflections = [];

        $extensions = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName());

        if (!is_array($extensions) || $extensions === []) {
            return $methodReflections;
        }

        /** @var array<class-string|null> $extensions */
        $extensions = array_unique($extensions);

        foreach ($extensions as $extension) {
            if ($extension === null) {
                continue;
            }

            $extensionClassName = $this->configurationResolver->resolveExtensionClassName($extension);

            if ($extensionClassName === null) {
                continue;
            }

            $classReflection = $this->reflectionProvider->getClass($extensionClassName);

            if (!$classReflection->isSubclassOfClass($this->reflectionProvider->getClass('SilverStripe\Core\Extension'))) {
                continue;
            }

            $reflectionMethods = $classReflection->getNativeReflection()->getMethods(
                ReflectionMethod::IS_PUBLIC
            );

            foreach ($reflectionMethods as $reflectionMethod) {
                if (!$classReflection->hasNativeMethod($reflectionMethod->getName())) {
                    continue;
                }

                $extendedMethodReflection = $classReflection->getNativeMethod($reflectionMethod->getName());

                if ($extendedMethodReflection->isStatic()) {
                    continue;
                }

                /** @var non-empty-lowercase-string $methodName */
                $methodName = strtolower($reflectionMethod->getName());

                $methodReflections[$methodName] = $extendedMethodReflection;
            }
        }

        return $methodReflections;
    }
}
