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
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private string $configurationPropertyName;
    /**
     * @readonly
     */
    private ConfigurationResolver $configurationResolver;
    /**
     * @readonly
     * @var int|true
     */
    private $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE;
    private TypeResolver $typeResolver;

    /**
     * @param true|int $excludeMiddleware
     */
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, string $configurationPropertyName, ConfigurationResolver $configurationResolver, $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->configurationPropertyName = $configurationPropertyName;
        $this->configurationResolver = $configurationResolver;
        /**
         * @var true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
         */
        $this->excludeMiddleware = $excludeMiddleware;
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return $this->configurationPropertyName;
    }

    /**
     * @return int|true
     */
    #[Override]
    public function getExcludeMiddleware()
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

    /**
     * @return static
     */
    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
