<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigurationResolverAwareInterface;
use Override;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Config\Middleware\Middleware as MiddlewareInterface;
use SilverStripe\Config\Middleware\MiddlewareCommon;
use function class_exists;
use function get_parent_class;
use function is_array;

final class InheritanceMiddleware implements MiddlewareInterface, ConfigurationResolverAwareInterface
{
    use MiddlewareCommon;

    private ConfigurationResolver $configurationResolver;

    public function __construct()
    {
        $this->setDisableFlag(ConfigurationResolver::UNINHERITED);
    }

    #[Override]
    public function getClassConfig($class, $excludeMiddleware, $next): array
    {
        // Skip if disabled
        $config = $next($class, $excludeMiddleware);

        if (!$this->enabled($excludeMiddleware)) {
            return $config;
        }

        // Skip if not a class or not parent class
        $parent = class_exists($class) ? get_parent_class($class) : null;

        if ($parent === false || $parent === null) {
            return $config;
        }

        // Merge with parent class
        $parentConfig = $this->configurationResolver->get($parent, null);

        if (!is_array($parentConfig) || $parentConfig === []) {
            return $config;
        }

        return Priority::mergeArray($config, $parentConfig);
    }

    #[Override]
    public function setConfigurationResolver(ConfigurationResolver $configurationResolver): static
    {
        $this->configurationResolver = $configurationResolver;

        return $this;
    }
}
