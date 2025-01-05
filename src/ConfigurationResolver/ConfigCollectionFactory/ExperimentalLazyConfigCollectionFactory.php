<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\ConfigCollectionFactory;

use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigCollectionFactoryInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryProviderInterface;
use Cambis\Silverstan\FileFinder\FileFinder;
use Composer\InstalledVersions;
use Override;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Collections\MemoryConfigCollection;
use SilverStripe\Config\Transformer\YamlTransformer;
use function class_exists;
use function constant;
use function defined;
use function extension_loaded;

final readonly class ExperimentalLazyConfigCollectionFactory implements ConfigCollectionFactoryInterface
{
    public function __construct(
        private FileFinder $fileFinder,
        private MiddlewareRegistryProviderInterface $middlewareRegistryProvider
    ) {
    }

    /**
     * Creates a memory config collection.
     *
     * Note that this collection is transformed via yaml only, private static properties are accessed lazily via middleware.
     */
    #[Override]
    public function create(): ConfigCollectionInterface
    {
        return (new MemoryConfigCollection())
            ->setMiddlewares($this->middlewareRegistryProvider->getRegistry()->getMiddlewares())
            ->transform([
                $this->getYamlTransformer(),
            ]);
    }

    private function getYamlTransformer(): YamlTransformer
    {
        return YamlTransformer::create(
            $this->fileFinder->getAppRootDirectory(),
            $this->fileFinder->getYamlConfigFiles()
        )
            ->addRule('classexists', static function (string $class): bool {
                return class_exists($class);
            })
            // Assume that the env var is set
            ->addRule('envvarset', static function (string $name, mixed $value = null): bool {
                return true;
            })
            ->addRule('constantdefined', static function (string $name, mixed $value = null): bool {
                if (!defined($name)) {
                    return false;
                }

                return constant($name) === $value;
            })
            // Assume that the env var is set
            ->addRule('envorconstant', static function (string $name, mixed $value = null): bool {
                return true;
            })
            // PHPStan should only be run in a dev environment
            ->addRule('environment', static function (string $env): bool {
                return $env === 'dev';
            })
            // Search installed composer packages for module
            ->addRule('moduleexists', static function (string $module): bool {
                return InstalledVersions::isInstalled($module);
            })
            ->addRule('extensionloaded', static function (string $extension): bool {
                return extension_loaded($extension);
            });
    }
}
