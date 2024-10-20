<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Reflection\ExtensibleMethodReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\TemplateTypeMap;
use ReflectionMethod;
use function array_unique;
use function is_array;

final readonly class ExtensionMethodReflectionResolver implements MethodReflectionResolverInterface
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private ConfigurationResolver $configurationResolver,
        private ReflectionProvider $reflectionProvider,
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
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
            return [];
        }

        $types = $this->typeResolver->resolveInjectedMethodTypesFromConfigurationProperty($classReflection, $this->getConfigurationPropertyName());

        $methodReflections = [];

        foreach ($types as $name => $type) {
            $methodReflections[$name] = new ExtensibleMethodReflection($name, $classReflection, $type, [], false, false, null, TemplateTypeMap::createEmpty());
        }

        $extensions = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName());

        if (!is_array($extensions) || $extensions === []) {
            return $methodReflections;
        }

        /** @var string[] $extensions */
        $extensions = array_unique($extensions);

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

                $methodReflections[$reflectionMethod->getName()] = $extendedMethodReflection;
            }
        }

        return $methodReflections;
    }
}
