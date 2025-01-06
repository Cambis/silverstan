<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use function is_array;

final class SimpleRelationMethodTypeResolver implements MethodTypeResolverInterface, TypeResolverAwareInterface
{
    private TypeResolver $typeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly string $configurationPropertyName,
        private readonly ConfigurationResolver $configurationResolver,
        /**
         * @var true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
         */
        private readonly true|int $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return $this->configurationPropertyName;
    }

    #[Override]
    public function getExcludeMiddleware(): true|int
    {
        return $this->excludeMiddleware;
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $properties = [];
        $relation = $this->configurationResolver->get($classReflection->getName(), $this->configurationPropertyName, $this->excludeMiddleware);

        if (!is_array($relation) || $relation === []) {
            return $properties;
        }

        /** @var string[] $relation */
        foreach ($relation as $fieldName => $fieldType) {
            $properties[$fieldName] = $this->typeResolver->resolveRelationFieldType($fieldType);
        }

        return $properties;
    }

    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver): static
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
