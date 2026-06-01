<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigurationResolverAwareInterface;
use Override;
use SilverStripe\Config\MergeStrategy\Priority;
use function class_exists;
use function get_parent_class;
use function is_array;

/**
 * Inspired by https://github.com/silverstripe/silverstripe-framework/blob/5/src/Core/Config/Middleware/InheritanceMiddleware.php.
 */
final class InheritanceMiddleware extends AbstractMiddleware implements ConfigurationResolverAwareInterface
{
    private ConfigurationResolver $configurationResolver;

    public function __construct()
    {
        parent::__construct(ConfigurationResolver::EXCLUDE_INHERITED);
    }

    /**
     * @param true|int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware
     * @phpstan-ignore-next-line method.childParameterType
     */
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
        $parentConfig = $this->configurationResolver->get($parent);

        if (!is_array($parentConfig) || $parentConfig === []) {
            return $config;
        }

        return Priority::mergeArray($config, $parentConfig);
    }

    /**
     * @return static
     */
    #[Override]
    public function setConfigurationResolver(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;

        return $this;
    }
}
