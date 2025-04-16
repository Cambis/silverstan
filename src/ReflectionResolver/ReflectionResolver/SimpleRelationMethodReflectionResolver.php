<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\Reflection\ExtensibleMethodReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;

final class SimpleRelationMethodReflectionResolver implements MethodReflectionResolverInterface
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

    public function getConfigurationPropertyName(): string
    {
        return $this->configurationPropertyName;
    }

    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }
        $types = $this->typeResolver->resolveInjectedMethodTypesFromConfigurationProperty($classReflection, $this->configurationPropertyName);
        $methodReflections = [];
        foreach ($types as $name => $type) {
            $methodReflections[$name] = new ExtensibleMethodReflection($name, $classReflection, $type, [], false, false, null, TemplateTypeMap::createEmpty());
        }
        return $methodReflections;
    }
}
