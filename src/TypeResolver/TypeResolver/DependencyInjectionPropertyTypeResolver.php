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
use function is_array;

final class DependencyInjectionPropertyTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
{
    private TypeResolver $typeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ConfigurationResolver $configurationResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'dependencies';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isInjectable($classReflection)) {
            return [];
        }

        $types = [];
        $dependencies = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName());

        if (!is_array($dependencies) || $dependencies === []) {
            return $types;
        }

        /** @var array<array<mixed>|bool|int|string> $dependencies */
        foreach ($dependencies as $fieldName => $fieldType) {
            $types[$fieldName] = $this->typeResolver->resolveDependencyFieldType($fieldType);
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
