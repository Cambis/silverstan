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

final class DBPropertyTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
{
    private TypeResolver $typeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
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
        return 'db';
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

        $db = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName(), $this->excludeMiddleware);

        if (!is_array($db) || $db === []) {
            return $properties;
        }

        /** @var class-string[] $db */
        foreach ($db as $fieldName => $fieldType) {
            $properties[$fieldName] = $this->typeResolver->resolveDBFieldType($classReflection, $fieldName, $fieldType);
        }

        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return $properties;
        }

        return [
            ...$properties,
            ...$this->resolve($classReflection->getParentClass()),
        ];
    }

    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver): static
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
