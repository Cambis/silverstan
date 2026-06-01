<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\Reflection\PropertyReflection\ExtensiblePropertyReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;

final class SimpleRelationPropertyReflectionResolver implements PropertyReflectionResolverInterface
{
    /**
     * @readonly
     */
    private string $configurationPropertyName;
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private TypeResolver $typeResolver;
    public function __construct(string $configurationPropertyName, ClassReflectionAnalyser $classReflectionAnalyser, TypeResolver $typeResolver)
    {
        $this->configurationPropertyName = $configurationPropertyName;
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->typeResolver = $typeResolver;
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return $this->configurationPropertyName;
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
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
