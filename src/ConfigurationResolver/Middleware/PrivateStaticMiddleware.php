<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionProperty;
use SilverStripe\Config\MergeStrategy\Priority;
use Throwable;
use function str_contains;

/**
 * Basic middleware used to resolve private static configuration properties.
 */
final class PrivateStaticMiddleware extends AbstractMiddleware
{
    public function __construct(
        private readonly ClassManifest $classManifest,
        private readonly ReflectionProvider $reflectionProvider
    ) {
        parent::__construct(ConfigurationResolver::EXCLUDE_PRIVATE_STATIC);
    }

    /**
     * @param true|int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware
     * @phpstan-ignore-next-line method.childParameterType
     */
    #[Override]
    public function getClassConfig($class, $excludeMiddleware, $next)
    {
        // Get base config
        $config = $next($class, $excludeMiddleware);

        if (!$this->enabled($excludeMiddleware)) {
            return $config;
        }

        // Skip if class is not in the manifest
        if (!$this->classManifest->hasClass($class)) {
            return $config;
        }

        if (!$this->reflectionProvider->hasClass($class)) {
            return $config;
        }

        $classReflection = $this->reflectionProvider->getClass($class);

        $nativePropertyReflections = $classReflection->getNativeReflection()->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_STATIC);
        $classConfig = [];

        foreach ($nativePropertyReflections as $nativePropertyReflection) {
            // Properties with the `@internal` annotation are not considered configuration properties
            if (str_contains($nativePropertyReflection->getBetterReflection()->getDocComment() ?? '', '@internal')) {
                continue;
            }

            // Accessing the value may throw an exception if the value does not exist
            try {
                $classConfig[$nativePropertyReflection->getName()] = $nativePropertyReflection->getValue();
            } catch (Throwable) {
                continue;
            }
        }

        return Priority::mergeArray($config, $classConfig);
    }
}
