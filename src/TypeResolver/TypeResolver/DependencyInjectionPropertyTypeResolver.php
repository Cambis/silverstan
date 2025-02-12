<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use PHPStan\Reflection\ClassReflection;
use function is_array;

final class DependencyInjectionPropertyTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
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
        return 'dependencies';
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
        if (!$this->classReflectionAnalyser->isInjectable($classReflection)) {
            return [];
        }
        $types = [];
        $dependencies = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName(), $this->excludeMiddleware);
        if (!is_array($dependencies) || $dependencies === []) {
            return $types;
        }
        /** @var array<array<mixed>|bool|int|string> $dependencies */
        foreach ($dependencies as $fieldName => $fieldType) {
            $types[$fieldName] = $this->typeResolver->resolveDependencyFieldType($fieldType);
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
