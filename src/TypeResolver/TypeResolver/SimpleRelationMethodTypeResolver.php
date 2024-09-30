<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use function is_array;

final readonly class SimpleRelationMethodTypeResolver implements MethodTypeResolverInterface
{
    public function __construct(
        private string $configurationPropertyName,
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private ConfigurationResolver $configurationResolver,
        private TypeResolver $typeResolver,
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
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $properties = [];
        $relation = $this->configurationResolver->get($classReflection->getName(), $this->configurationPropertyName);

        if (!is_array($relation) || $relation === []) {
            return $properties;
        }

        /** @var string[] $relation */
        foreach ($relation as $fieldName => $fieldType) {
            $properties[$fieldName] = $this->typeResolver->resolveRelationFieldType($fieldType);
        }

        return $properties;
    }
}
