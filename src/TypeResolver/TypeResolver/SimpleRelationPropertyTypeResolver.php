<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\IntegerType;
use function array_keys;
use function is_array;

final readonly class SimpleRelationPropertyTypeResolver implements PropertyTypeResolverInterface
{
    public function __construct(
        private string $configurationPropertyName,
        private ConfigurationResolver $configurationResolver,
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
        $types = [];
        $relation = $this->configurationResolver->get($classReflection->getName(), $this->configurationPropertyName);

        if (!is_array($relation) || $relation === []) {
            return $types;
        }

        /** @var string[] $relationKeys */
        $relationKeys = array_keys($relation);

        foreach ($relationKeys as $fieldName) {
            $types[$fieldName . 'ID'] = new IntegerType();
        }

        return $types;
    }
}