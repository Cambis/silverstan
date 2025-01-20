<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use PHPStan\Reflection\ClassReflection;

final class FixedFieldsPropertyTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
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

    public function getConfigurationPropertyName(): string
    {
        return 'fixed_fields';
    }

    /**
     * @return int|true
     */
    public function getExcludeMiddleware()
    {
        return $this->excludeMiddleware;
    }

    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }
        $fixedFields = (array) $this->configurationResolver->get('SilverStripe\ORM\DataObject', $this->getConfigurationPropertyName(), $this->excludeMiddleware);
        $types = [];
        /** @var class-string[] $fixedFields */
        foreach ($fixedFields as $fieldName => $fieldType) {
            $types[$fieldName] = $this->typeResolver->resolveDBFieldType($classReflection, $fieldName, $fieldType);
        }
        return $types;
    }

    /**
     * @return static
     */
    public function setTypeResolver(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
        return $this;
    }
}
