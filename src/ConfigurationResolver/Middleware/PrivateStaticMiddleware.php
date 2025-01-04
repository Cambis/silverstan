<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionProperty;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Config\Middleware\Middleware as MiddlewareInterface;
use SilverStripe\Config\Middleware\MiddlewareCommon;
use Throwable;
use function str_contains;

/**
 * Basic middleware used to resolve private static configuration properties.
 */
final class PrivateStaticMiddleware implements MiddlewareInterface
{
    use MiddlewareCommon;

    public function __construct(
        private readonly ClassManifest $classManifest,
        private readonly ReflectionProvider $reflectionProvider
    ) {
        $this->setDisableFlag(ConfigurationResolver::EXCLUDE_PRIVATE_STATIC);
    }

    #[Override]
    public function getClassConfig($class, $excludeMiddleware, $next)
    {
        // Get base config
        $config = $next($class, $excludeMiddleware);

        if (!$this->enabled($excludeMiddleware)) {
            return $config;
        }

        // Skip if class is not in the manifest
        if (!$this->classManifest->classMap->hasClass($class)) {
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
