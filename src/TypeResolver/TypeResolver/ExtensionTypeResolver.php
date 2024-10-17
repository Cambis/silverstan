<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use function array_unique;
use function is_array;

final class ExtensionTypeResolver implements MethodTypeResolverInterface, PropertyTypeResolverInterface
{
    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ConfigurationResolver $configurationResolver,
        private readonly ReflectionProvider $reflectionProvider,
        private readonly TypeFactory $typeFactory
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

            $types[] = $this->typeFactory->createExtensibleTypeFromType(
                new ObjectType($extensionClassName, null, $classReflection)
            );
        }

        return $types;
    }
}
