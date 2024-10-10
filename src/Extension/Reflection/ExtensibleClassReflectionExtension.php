<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Override;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\ShouldNotHappenException;
use function array_key_exists;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ExtensibleClassReflectionExtensionTest
 */
final class ExtensibleClassReflectionExtension implements MethodsClassReflectionExtension, PropertiesClassReflectionExtension
{
    /**
     * @var PropertyReflection[][]
     */
    private array $propertyReflections = [];

    /**
     * @var MethodReflection[][]
     */
    private array $methodReflections = [];

    public function __construct(
        private readonly AnnotationsMethodsClassReflectionExtension $annotationsMethodsClassReflectionExtension,
        private readonly AnnotationsPropertiesClassReflectionExtension $annotationsPropertiesClassReflectionExtension,
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ReflectionResolver $reflectionResolver,
    ) {
    }

    #[Override]
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        // Skip non-extensible classes
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
            return false;
        }

        // Let PHPStan handle this case
        if ($classReflection->hasNativeMethod($methodName)) {
            return false;
        }

        if ($this->annotationsMethodsClassReflectionExtension->hasMethod($classReflection, $methodName)) {
            return true;
        }

        // // Let PHPStan handle this case
        // if (array_key_exists($methodName, $classReflection->getMethodTags())) {
        //     return false;
        // }

        $methodReflections = $this->resolveInjectedMethodReflections($classReflection);

        $methodReflection = $methodReflections[$methodName] ?? null;

        return $methodReflection instanceof MethodReflection;
    }

    #[Override]
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        // Skip non-extensible classes
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
            return false;
        }

        // Let PHPStan handle this case
        if ($classReflection->hasNativeProperty($propertyName)) {
            return false;
        }

        if ($this->annotationsPropertiesClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return true;
        }

        // // Let PHPStan handle this case
        // if (array_key_exists($propertyName, $classReflection->getPropertyTags())) {
        //     return false;
        // }

        $propertyReflections = $this->resolveInjectedPropertyReflections($classReflection);
        $propertyReflection = $propertyReflections[$propertyName] ?? null;

        return $propertyReflection instanceof PropertyReflection;
    }

    #[Override]
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        if ($this->annotationsMethodsClassReflectionExtension->hasMethod($classReflection, $methodName)) {
            return $this->annotationsMethodsClassReflectionExtension->getMethod($classReflection, $methodName);
        }

        $methodReflections = $this->resolveInjectedMethodReflections($classReflection);
        $methodReflection = $methodReflections[$methodName] ?? null;

        if (!$methodReflection instanceof MethodReflection) {
            throw new ShouldNotHappenException();
        }

        return $methodReflection;
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        if ($this->annotationsPropertiesClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return $this->annotationsPropertiesClassReflectionExtension->getProperty($classReflection, $propertyName);
        }

        $propertyReflections = $this->resolveInjectedPropertyReflections($classReflection);
        $propertyReflection = $propertyReflections[$propertyName] ?? null;

        if (!$propertyReflection instanceof PropertyReflection) {
            throw new ShouldNotHappenException();
        }

        return $propertyReflection;
    }

    /**
     * @return MethodReflection[]
     */
    private function resolveInjectedMethodReflections(ClassReflection $classReflection): array
    {
        if (!array_key_exists($classReflection->getCacheKey(), $this->methodReflections)) {
            $this->methodReflections[$classReflection->getCacheKey()] = $this->reflectionResolver->resolveInjectedMethodReflections($classReflection);
        }

        return $this->methodReflections[$classReflection->getCacheKey()];
    }

    /**
     * @return PropertyReflection[]
     */
    private function resolveInjectedPropertyReflections(ClassReflection $classReflection): array
    {
        if (!array_key_exists($classReflection->getCacheKey(), $this->propertyReflections)) {
            $this->propertyReflections[$classReflection->getCacheKey()] = $this->reflectionResolver->resolveInjectedPropertyReflections($classReflection);
        }

        return $this->propertyReflections[$classReflection->getCacheKey()];
    }
}
