<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigurationResolverAwareInterface;
use PHPStan\Reflection\ReflectionProvider;
use SilverStripe\Config\MergeStrategy\Priority;
use Throwable;
use function is_array;

/**
 * Inspired by https://github.com/silverstripe/silverstripe-framework/blob/5/src/Core/Config/Middleware/ExtensionMiddleware.php.
 */
final class ExtensionMiddleware extends AbstractMiddleware implements ConfigurationResolverAwareInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        ReflectionProvider $reflectionProvider
    ) {
        $this->reflectionProvider = $reflectionProvider;
        parent::__construct(ConfigurationResolver::EXCLUDE_EXTRA_SOURCES);
    }

    /**
     * @param true|int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware
     * @phpstan-ignore-next-line method.childParameterType
     */
    public function getClassConfig($class, $excludeMiddleware, $next)
    {
        // Get base config
        $config = $next($class, $excludeMiddleware);
        if (!$this->enabled($excludeMiddleware)) {
            return $config;
        }
        /** @var int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware */
        $extraConfig = $this->getExtraConfig($class, $excludeMiddleware);
        foreach ($extraConfig as $extra) {
            $config = Priority::mergeArray($config, $extra);
        }
        return $config;
    }

    /**
     * @return static
     */
    public function setConfigurationResolver(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;
        return $this;
    }

    /**
     * Applied config to a class from its extensions.
     *
     * @param class-string $class
     * @param int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware
     * @return iterable<mixed[]>
     */
    private function getExtraConfig(string $class, int $excludeMiddleware): iterable
    {
        /** @var int-mask-of<ConfigurationResolver::EXCLUDE_*> $mask */
        $mask = ConfigurationResolver::EXCLUDE_INHERITED | $excludeMiddleware | $this->disableFlag;

        $extensionSourceConfig = $this->configurationResolver->get($class, null, $mask);

        if (!is_array($extensionSourceConfig) || $extensionSourceConfig === []) {
            return;
        }

        if (!isset($extensionSourceConfig['extensions'])) {
            return;
        }

        /** @var array{extensions: array<string|null>} $extensionSourceConfig */
        $extensions = $extensionSourceConfig['extensions'];

        foreach ($extensions as $extension) {
            // Allow removing extensions via yaml config by setting named extension config to null
            if ($extension === null) {
                continue;
            }

            $extension = $this->configurationResolver->resolveExtensionClassName($extension);

            if ($extension === null) {
                continue;
            }

            $extensionClass = $this->reflectionProvider->getClass($extension);

            // Check class hierarchy from root up
            foreach ($extensionClass->getAncestors() as $extensionClassParent) {
                if (!$extensionClassParent->isSubclassOf('SilverStripe\Core\Extension')) {
                    continue;
                }

                // Skip base class
                if ($extensionClass->getName() === 'SilverStripe\ORM\DataExtension') {
                    continue;
                }

                // Merge config from extension
                $extensionConfig = $this->configurationResolver->get(
                    $extensionClassParent->getName(),
                    null,
                    ConfigurationResolver::EXCLUDE_EXTRA_SOURCES | ConfigurationResolver::EXCLUDE_INHERITED
                );

                if (is_array($extensionConfig) && $extensionConfig !== []) {
                    yield $extensionConfig;
                }

                // Check the `get_extra_config` method
                if (!$extensionClassParent->hasNativeMethod('get_extra_config')) {
                    continue;
                }

                // Attempt to execute the method
                try {
                    $extraConfig = $extensionClassParent->getName()::get_extra_config($class, $extensionClass->getName(), []);
                } catch (Throwable $exception) {
                    continue;
                }

                if (is_array($extraConfig) && $extraConfig !== []) {
                    yield $extraConfig;
                }
            }
        }
    }
}
