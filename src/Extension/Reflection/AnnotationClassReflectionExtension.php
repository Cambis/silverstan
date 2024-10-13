<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use function array_key_exists;

final class AnnotationClassReflectionExtension implements PropertiesClassReflectionExtension
{
    /**
     * @var PropertyReflection[][]
     */
    private array $propertyReflections = [];

    public function __construct(
        private readonly ReflectionResolver $reflectionResolver
    ) {
    }

    #[Override]
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (!array_key_exists($classReflection->getCacheKey(), $this->propertyReflections)) {
            $this->propertyReflections[$classReflection->getCacheKey()] = [];
        }

        if (!array_key_exists($propertyName, $this->propertyReflections[$classReflection->getCacheKey()])) {
            $property = $this->reflectionResolver->resolveAnnotationPropertyReflection($classReflection, $classReflection, $propertyName);

            if (!$property instanceof PropertyReflection) {
                return false;
            }

            $this->propertyReflections[$classReflection->getCacheKey()][$propertyName] = $property;
        }

        return true;
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return $this->propertyReflections[$classReflection->getCacheKey()][$propertyName];
    }
}
