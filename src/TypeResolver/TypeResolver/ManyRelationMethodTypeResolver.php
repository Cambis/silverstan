<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\GenericObjectType;
use function array_key_exists;
use function is_array;

final readonly class ManyRelationMethodTypeResolver implements MethodTypeResolverInterface
{
    public function __construct(
        private string $configurationPropertyName,
        private string $listName,
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private ConfigurationResolver $configurationResolver,
        private TypeFactory $typeFactory,
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

        $types = [];

        $relation = $this->configurationResolver->get($classReflection->getName(), $this->configurationPropertyName);

        if (!is_array($relation) || $relation === []) {
            return $types;
        }

        /** @var array<string|string[]> $relation */
        foreach ($relation as $fieldName => $fieldType) {
            $relationFieldType = $this->typeResolver->resolveRelationFieldType($fieldType);

            if ($relationFieldType->isObject()->no()) {
                continue;
            }

            $listName = $this->listName;

            if (
                is_array($fieldType) &&
                array_key_exists('through', $fieldType) && $listName === 'SilverStripe\ORM\ManyManyList'
            ) {
                $listName = 'SilverStripe\ORM\ManyManyThroughList';
            }

            $types[$fieldName] = new GenericObjectType(
                $listName,
                [$this->typeFactory->createExtensibleTypeFromType($relationFieldType)],
            );
        }

        return $types;
    }
}
