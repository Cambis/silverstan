<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\Reflection\ExtensibleMethodReflection;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;

final readonly class SimpleRelationMethodReflectionResolver implements MethodReflectionResolverInterface
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

        $types = $this->typeResolver->resolveInjectedMethodTypesFromConfigurationProperty($classReflection, $this->configurationPropertyName);

        $methodReflections = [];

        foreach ($types as $name => $type) {
            $methodReflections[$name] = new ExtensibleMethodReflection($name, $classReflection, $type);
        }

        return $methodReflections;
    }
}
