<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionMethod;
use function array_unique;
use function is_array;

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

    public function getConfigurationPropertyName(): string
    {
        return 'extensions';
    }

    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
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

            if (!$classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
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

                $methodReflections[$reflectionMethod->getName()] = $extendedMethodReflection;
            }
        }
        return $methodReflections;
    }
}
