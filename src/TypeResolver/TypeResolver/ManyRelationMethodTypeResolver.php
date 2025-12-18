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
use PHPStan\Type\Generic\GenericObjectType;
use function array_key_exists;
use function is_array;

final class ManyRelationMethodTypeResolver implements MethodTypeResolverInterface, TypeResolverAwareInterface
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
     */
    private string $listName;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     * @var int|true
     */
    private $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE;
    private TypeResolver $typeResolver;

    /**
     * @param true|int $excludeMiddleware
     */
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, string $configurationPropertyName, ConfigurationResolver $configurationResolver, string $listName, TypeFactory $typeFactory, $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->configurationPropertyName = $configurationPropertyName;
        $this->configurationResolver = $configurationResolver;
        $this->listName = $listName;
        $this->typeFactory = $typeFactory;
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
