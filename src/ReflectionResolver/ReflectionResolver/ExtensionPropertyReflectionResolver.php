<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;

final readonly class ExtensionPropertyReflectionResolver implements PropertyReflectionResolverInterface
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private ReflectionResolver $reflectionResolver,
        private TypeResolver $typeResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'extensions';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classAnalyser->isExtensible($classReflection)) {
            return [];
        }

        $extensionTypes = $this->typeResolver->resolveInjectedPropertyTypesFromConfigurationProperty($classReflection, $this->getConfigurationPropertyName());
        $propertyReflections = [];

        foreach ($extensionTypes as $type) {
            if ($type->isObject()->no()) {
                continue;
            }

            foreach ($type->getObjectClassReflections() as $objectClassReflection) {
                $propertyReflections = [...$propertyReflections, ...$this->reflectionResolver->resolveInjectedPropertyReflections($objectClassReflection)];
            }
        }

        return $propertyReflections;
    }
}
