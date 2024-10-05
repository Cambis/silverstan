<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionMethod;
use function array_unique;
use function is_array;

final class ExtensionMethodTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
{
    private TypeResolver $typeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ConfigurationResolver $configurationResolver,
        private readonly ReflectionProvider $reflectionProvider,
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
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
            return [];
        }

        $extensions = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName());

        if (!is_array($extensions) || $extensions === []) {
            return [];
        }

        /** @var string[] $extensions */
        $extensions = array_unique($extensions);
        $types = [];

        foreach ($extensions as $extension) {
            $extensionClassName = $this->configurationResolver->resolveExtensionClassName($extension);

            if ($extensionClassName === null) {
                continue;
            }

            $classReflection = $this->reflectionProvider->getClass($extensionClassName);

            if (!$classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                continue;
            }

            $reflectionMethods = $classReflection->getNativeReflection()->getMethods(
                ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
            );

            foreach ($reflectionMethods as $reflectionMethod) {
                if (!$classReflection->hasNativeMethod($reflectionMethod->getName())) {
                    continue;
                }

                $extendedMethodReflection = $classReflection->getNativeMethod($reflectionMethod->getName());

                $types[$reflectionMethod->getName()] = ParametersAcceptorSelector::selectSingle($extendedMethodReflection->getVariants())->getReturnType();
            }

            $types = [...$types, ...$this->typeResolver->resolveInjectedMethodTypes($classReflection)];
        }

        return $types;
    }

    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver): static
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
