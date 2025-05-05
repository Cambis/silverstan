<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\Reflection\ExtensiblePropertyReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use PHPStan\Reflection\ClassReflection;

final class DependencyInjectionPropertyReflectionResolver implements PropertyReflectionResolverInterface
{
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private TypeResolver $typeResolver;
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, TypeResolver $typeResolver)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->typeResolver = $typeResolver;
    }

    public function getConfigurationPropertyName(): string
    {
        return 'dependencies';
    }

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
