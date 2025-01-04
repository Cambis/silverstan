<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionProperty;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Config\Middleware\Middleware as MiddlewareInterface;
use SilverStripe\Config\Middleware\MiddlewareCommon;
use function str_contains;

/**
 * Basic middleware used to resolve private static configuration properties.
 */
final class PrivateStaticMiddleware implements MiddlewareInterface
{
    use MiddlewareCommon;

    public function __construct(
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

        if (!$this->reflectionProvider->hasClass($class)) {
            return $config;
        }

        $classReflection = $this->reflectionProvider->getClass($class);

        $nativePropertyReflections = $classReflection->getNativeReflection()->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_STATIC);
        $classConfig = [];

        foreach ($nativePropertyReflections as $nativePropertyReflection) {
            if (str_contains($nativePropertyReflection->getBetterReflection()->getDocComment() ?? '', '@internal')) {
                continue;
            }

            $classConfig[$nativePropertyReflection->getName()] = $nativePropertyReflection->getValue();
        }

        return Priority::mergeArray($config, $classConfig);
    }
}
