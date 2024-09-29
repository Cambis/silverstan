<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use ReflectionMethod;

final readonly class ExtensionMethodReflectionResolver implements MethodReflectionResolverInterface
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
        $methodReflections = [];

        foreach ($extensionTypes as $type) {
            if ($type->isObject()->no()) {
                continue;
            }

            foreach ($type->getObjectClassReflections() as $objectClassReflection) {
                $methodReflections = [...$methodReflections, ...$this->reflectionResolver->resolveInjectedMethodReflections($objectClassReflection)];

                $reflectionMethods = $objectClassReflection->getNativeReflection()->getMethods(
                    ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
                );

                foreach ($reflectionMethods as $reflectionMethod) {
                    if ($reflectionMethod->isStatic()) {
                        continue;
                    }

                    if (!$objectClassReflection->hasNativeMethod($reflectionMethod->getName())) {
                        continue;
                    }

                    $methodReflections[$reflectionMethod->getName()] = $objectClassReflection->getNativeMethod($reflectionMethod->getName());
                }
            }
        }

        return $methodReflections;
    }
}
