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
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
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
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, ConfigurationResolver $configurationResolver, $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->configurationResolver = $configurationResolver;
        /**
         * @var true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
         */
        $this->excludeMiddleware = $excludeMiddleware;
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'db';
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

        $types = [];

        $db = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName(), $this->excludeMiddleware);

        if (!is_array($db) || $db === []) {
            return $types;
        }

        /** @var class-string[] $db */
        foreach ($db as $fieldName => $fieldType) {
            $types[$fieldName] = $this->typeResolver->resolveDBFieldType($fieldType);
        }

        return $types;
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
