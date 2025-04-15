<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use function array_key_exists;
use function is_array;

final class ManyRelationMethodTypeResolver implements MethodTypeResolverInterface, TypeResolverAwareInterface
{
    private TypeResolver $typeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly string $configurationPropertyName,
        private readonly ConfigurationResolver $configurationResolver,
        private readonly string $listName,
        private readonly ReflectionProvider $reflectionProvider,
        private readonly TypeFactory $typeFactory,
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

        if (!$this->reflectionProvider->hasClass($this->listName)) {
            return [];
        }

        $types = [];

        $relation = $this->configurationResolver->get($classReflection->getName(), $this->configurationPropertyName, $this->excludeMiddleware);

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

    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver): static
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
