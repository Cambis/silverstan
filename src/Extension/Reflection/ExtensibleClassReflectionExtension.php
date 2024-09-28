<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\Reflection;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\ShouldNotHappenException;
use function array_key_exists;

/**
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ExtensibleClassReflectionExtensionTest
 */
final class ExtensibleClassReflectionExtension implements PropertiesClassReflectionExtension
{
    /**
     * @var PropertyReflection[][]
     */
    private array $propertyReflections = [];

    public function __construct(
        private readonly ClassAnalyser $classAnalyser,
        private readonly ReflectionResolver $reflectionResolver,
    ) {
    }

    #[Override]
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        // Skip non-extensible classes
        if (!$this->classAnalyser->isExtensible($classReflection)) {
            return false;
        }

        // Let PHPStan handle this case
        if ($classReflection->hasNativeProperty($propertyName)) {
            return false;
        }

        // Let PHPStan handle this case
        if (array_key_exists($propertyName, $classReflection->getPropertyTags())) {
            return false;
        }

        $propertyReflections = $this->resolveInjectedPropertyReflections($classReflection);
        $propertyReflection = $propertyReflections[$propertyName] ?? null;

        return $propertyReflection instanceof PropertyReflection;
    }

    #[Override]
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $propertyReflections = $this->resolveInjectedPropertyReflections($classReflection);
        $propertyReflection = $propertyReflections[$propertyName] ?? null;

        if (!$propertyReflection instanceof PropertyReflection) {
            throw new ShouldNotHappenException();
        }

        return $propertyReflection;
    }

    /**
     * @return PropertyReflection[]
     */
    private function resolveInjectedPropertyReflections(ClassReflection $classReflection): array
    {
        if (!array_key_exists($classReflection->getName(), $this->propertyReflections)) {
            $this->propertyReflections[$classReflection->getName()] = $this->reflectionResolver->resolveInjectedPropertyReflections($classReflection);
        }

        return $this->propertyReflections[$classReflection->getName()];
    }
}
