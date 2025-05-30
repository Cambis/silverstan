<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\Reflection\PropertyReflection\ExtensiblePropertyReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;

final readonly class DependencyInjectionPropertyReflectionResolver implements PropertyReflectionResolverInterface
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private TypeResolver $typeResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'dependencies';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isInjectable($classReflection)) {
            return [];
        }

        $propertyReflections = [];

        $types = $this->typeResolver->resolveInjectedPropertyTypesFromConfigurationProperty($classReflection, $this->getConfigurationPropertyName());

        foreach ($types as $name => $type) {
            if ($classReflection->hasNativeProperty($name)) {
                continue;
            }

            $propertyReflections[$name] = new ExtensiblePropertyReflection($classReflection, $type, $type);
        }

        return $propertyReflections;
    }
}
