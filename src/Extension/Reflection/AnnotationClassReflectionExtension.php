<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use function array_key_exists;
use function strtolower;

/**
 * This extension is used resolve methods and properties declared by annotations.
 *
 * Inspired by https://github.com/phpstan/phpstan-src/blob/1.12.x/src/Reflection/Annotations/AnnotationsPropertiesClassReflectionExtension.php, abeit a simpled version of it.
 *
 * We are not using the former as it is not covered by the backward compatibility promise.
 *
 * @see https://phpstan.org/developing-extensions/backward-compatibility-promise
 */
final class AnnotationClassReflectionExtension implements MethodsClassReflectionExtension, PropertiesClassReflectionExtension
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @var MethodReflection[][]
     */
    private array $methodReflections = [];

    /**
     * @var PropertyReflection[][]
     */
    private array $propertyReflections = [];

    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (!array_key_exists($classReflection->getCacheKey(), $this->methodReflections)) {
            $this->methodReflections[$classReflection->getCacheKey()] = [];
        }
        if (!array_key_exists($methodName, $this->methodReflections[$classReflection->getCacheKey()])) {
            $methodReflection = $this->reflectionResolver->resolveAnnotationMethodReflection($classReflection, $classReflection, $methodName);

            if (!$methodReflection instanceof MethodReflection) {
                return false;
            }

            $this->methodReflections[$classReflection->getCacheKey()][strtolower($methodName)] = $methodReflection;
        }
        return true;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (!array_key_exists($classReflection->getCacheKey(), $this->propertyReflections)) {
            $this->propertyReflections[$classReflection->getCacheKey()] = [];
        }
        if (!array_key_exists($propertyName, $this->propertyReflections[$classReflection->getCacheKey()])) {
            $propertyReflection = $this->reflectionResolver->resolveAnnotationPropertyReflection($classReflection, $classReflection, $propertyName);

            if (!$propertyReflection instanceof PropertyReflection) {
                return false;
            }

            $this->propertyReflections[$classReflection->getCacheKey()][$propertyName] = $propertyReflection;
        }
        return true;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return $this->propertyReflections[$classReflection->getCacheKey()][$propertyName];
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->methodReflections[$classReflection->getCacheKey()][strtolower($methodName)];
    }
}
