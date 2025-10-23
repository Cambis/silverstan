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

final class SimpleRelationPropertyTypeResolver implements PropertyTypeResolverInterface
{
    /**
     * @readonly
     */
    private ConfigurationResolver $configurationResolver;
    /**
     * @readonly
     */
    private string $configurationPropertyName;
    /**
     * @readonly
     * @var int|true
     */
    private $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE;
    /**
     * @param true|int $excludeMiddleware
     */
    public function __construct(ConfigurationResolver $configurationResolver, string $configurationPropertyName, $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE)
    {
        $this->configurationResolver = $configurationResolver;
        $this->configurationPropertyName = $configurationPropertyName;
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
        $types = [];
        $relation = $this->configurationResolver->get($classReflection->getName(), $this->configurationPropertyName, $this->excludeMiddleware);

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
