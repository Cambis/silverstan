<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use function array_unique;
use function is_array;

final class ExtensionMethodTypeResolver implements MethodTypeResolverInterface, TypeResolverAwareInterface
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
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     * @var int|true
     */
    private $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE;
    private TypeResolver $typeResolver;

    /**
     * @param true|int $excludeMiddleware
     */
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, ConfigurationResolver $configurationResolver, ReflectionProvider $reflectionProvider, $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->configurationResolver = $configurationResolver;
        $this->reflectionProvider = $reflectionProvider;
        /**
         * @var true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
         */
        $this->excludeMiddleware = $excludeMiddleware;
    }

    public function getConfigurationPropertyName(): string
    {
        return 'extensions';
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
        if (!$this->classReflectionAnalyser->isExtensible($classReflection)) {
            return [];
        }
        $extensions = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName(), $this->excludeMiddleware);
        if (!is_array($extensions) || $extensions === []) {
            return [];
        }
        /** @var array<class-string|null> $extensions */
        $extensions = array_unique($extensions);
        $types = [];
        foreach ($extensions as $extension) {
            if ($extension === null) {
                continue;
            }

            $extensionClassName = $this->configurationResolver->resolveExtensionClassName($extension);

            if ($extensionClassName === null) {
                continue;
            }

            $classReflection = $this->reflectionProvider->getClass($extensionClassName);

            if (!$classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                continue;
            }

            $types = array_merge($types, $this->typeResolver->resolveInjectedMethodTypes($classReflection));
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
