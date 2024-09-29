<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\Reflection\ExtensiblePropertyReflection;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;

final readonly class SimpleRelationPropertyReflectionResolver implements PropertyReflectionResolverInterface
{
    public function __construct(
        private string $configurationPropertyName,
        private ClassAnalyser $classAnalyser,
        private TypeResolver $typeResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return $this->configurationPropertyName;
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $types = $this->typeResolver->resolveInjectedPropertyTypesFromConfigurationProperty($classReflection, $this->configurationPropertyName);

        $propertyReflections = [];

        foreach ($types as $name => $type) {
            $propertyReflections[$name] = new ExtensiblePropertyReflection($classReflection, $type, $type);
        }

        return $propertyReflections;
    }
}
