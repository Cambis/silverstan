<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use function array_key_exists;

/**
 * This extension resolves `SilverStripe\Core\Extensible` magic methods and properties.
 *
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ExtensibleClassReflectionExtensionTest
 */
final class ExtensibleClassReflectionExtension implements MethodsClassReflectionExtension, PropertiesClassReflectionExtension
{
    /**
     * @var MethodReflection[][]
     */
    private array $methodReflections = [];

    /**
     * @var PropertyReflection[][]
     */
    private array $propertyReflections = [];

    public function __construct(
        private readonly AnnotationClassReflectionExtension $annotationClassReflectionExtension,
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ReflectionResolver $reflectionResolver
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

        if ($this->annotationClassReflectionExtension->hasMethod($classReflection, $methodName)) {
            return true;
        }

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

        if ($this->annotationClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return true;
        }

        $propertyReflections = $this->resolveInjectedPropertyReflections($classReflection);
        $propertyReflection = $propertyReflections[$propertyName] ?? null;

        return $propertyReflection instanceof PropertyReflection;
    }

    #[Override]
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        if ($this->annotationClassReflectionExtension->hasMethod($classReflection, $methodName)) {
            return $this->annotationClassReflectionExtension->getMethod($classReflection, $methodName);
        }

        return $this->methodReflections[$classReflection->getCacheKey()][$methodName];
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        if ($this->annotationClassReflectionExtension->hasProperty($classReflection, $propertyName)) {
            return $this->annotationClassReflectionExtension->getProperty($classReflection, $propertyName);
        }

        return $this->propertyReflections[$classReflection->getCacheKey()][$propertyName];
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
