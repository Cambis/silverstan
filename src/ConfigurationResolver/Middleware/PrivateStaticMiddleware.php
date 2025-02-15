<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Override;
use ReflectionClass;
use ReflectionProperty;
use SilverStripe\Config\MergeStrategy\Priority;
use Throwable;
use function class_exists;
use function str_contains;

/**
 * Basic middleware used to resolve private static configuration properties.
 */
final class PrivateStaticMiddleware extends AbstractMiddleware
{
    public function __construct(
        private readonly ClassManifest $classManifest
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

        // Skip if class does not exist!
        if (!class_exists($class)) {
            return $config;
        }

        $classReflection = new ReflectionClass($class);

        $nativePropertyReflections = $classReflection->getProperties(ReflectionProperty::IS_PRIVATE);
        $classConfig = [];

        foreach ($nativePropertyReflections as $nativePropertyReflection) {
            if (!$nativePropertyReflection->isStatic()) {
                continue;
            }

            $docComment = $nativePropertyReflection->getDocComment() === false ? '' : $nativePropertyReflection->getDocComment();

            // Properties with the `@internal` annotation are not considered configuration properties
            if (str_contains($docComment, '@internal')) {
                continue;
            }

            // Accessing the value may throw an exception if the value does not exist
            try {
                $nativePropertyReflection->setAccessible(true);
                $classConfig[$nativePropertyReflection->getName()] = $nativePropertyReflection->getValue();
            } catch (Throwable) {
                continue;
            }
        }

        return Priority::mergeArray($config, $classConfig);
    }
}
