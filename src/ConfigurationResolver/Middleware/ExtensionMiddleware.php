<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigurationResolverAwareInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareInterface;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Config\Middleware\MiddlewareCommon;
use Throwable;
use function is_array;

final class ExtensionMiddleware implements MiddlewareInterface, ConfigurationResolverAwareInterface
{
    use MiddlewareCommon;

    private ConfigurationResolver $configurationResolver;

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {
        $this->setDisableFlag(ConfigurationResolver::EXCLUDE_EXTRA_SOURCES);
    }

    #[Override]
    public function getClassConfig($class, $excludeMiddleware, $next)
    {
        // Get base config
        $config = $next($class, $excludeMiddleware);

        if (!$this->enabled($excludeMiddleware)) {
            return $config;
        }

        foreach ($this->getExtraConfig($class, $excludeMiddleware) as $extra) {
            $config = Priority::mergeArray($config, $extra);
        }

        return $config;
    }

    #[Override]
    public function setConfigurationResolver(ConfigurationResolver $configurationResolver): static
    {
        $this->configurationResolver = $configurationResolver;

        return $this;
    }

    /**
     * Applied config to a class from its extensions.
     *
     * @param class-string $class
     * @return iterable<mixed[]>
     */
    private function getExtraConfig(string $class, int|true $excludeMiddleware): iterable
    {
        $extensionSourceConfig = $this->configurationResolver->get($class, null, ConfigurationResolver::UNINHERITED | $excludeMiddleware | $this->disableFlag);

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
                throw new ShouldNotHappenException();
            }

            $extensionClass = $this->reflectionProvider->getClass($extension);

            // Check class hierarchy from root up
            foreach ($extensionClass->getAncestors() as $extensionClassParent) {
                // Ancestors start at the current class
                if ($extensionClass === $extensionClassParent) {
                    continue;
                }

                // Skip base classes
                if ($extensionClassParent->is('SilverStripe\Core\Extension')) {
                    continue;
                }

                if ($extensionClass->is('SilverStripe\ORM\DataExtension')) {
                    continue;
                }

                // Merge config from extension
                $extensionConfig = $this->configurationResolver->get(
                    $extensionClassParent->getName(),
                    null,
                    ConfigurationResolver::EXCLUDE_EXTRA_SOURCES | ConfigurationResolver::UNINHERITED
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
                } catch (Throwable) {
                    continue;
                }

                if (is_array($extraConfig) && $extraConfig !== []) {
                    yield $extraConfig;
                }
            }
        }
    }
}
