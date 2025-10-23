<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\ConfigCollectionFactory;

use Closure;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigCollectionFactoryInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryProviderInterface;
use Cambis\Silverstan\FileFinder\FileFinder;
use Composer\InstalledVersions;
use Override;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Collections\MemoryConfigCollection;
use SilverStripe\Config\Transformer\YamlTransformer;
use function constant;
use function defined;

final class LazyConfigCollectionFactory implements ConfigCollectionFactoryInterface
{
    /**
     * @readonly
     */
    private FileFinder $fileFinder;
    /**
     * @readonly
     */
    private MiddlewareRegistryProviderInterface $middlewareRegistryProvider;
    public function __construct(FileFinder $fileFinder, MiddlewareRegistryProviderInterface $middlewareRegistryProvider)
    {
        $this->fileFinder = $fileFinder;
        $this->middlewareRegistryProvider = $middlewareRegistryProvider;
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
            ->addRule('classexists', Closure::fromCallable('class_exists'))
            // Assume that the env var is set
            ->addRule('envvarset', static function (string $name, $value = null): bool {
                return true;
            })
            ->addRule('constantdefined', static function (string $name, $value = null): bool {
                if (!defined($name)) {
                    return false;
                }

                return constant($name) === $value;
            })
            // Assume that the env var is set
            ->addRule('envorconstant', static function (string $name, $value = null): bool {
                return true;
            })
            // PHPStan should only be run in a dev environment
            ->addRule('environment', static function (string $env): bool {
                return $env === 'dev';
            })
            // Search installed composer packages for module
            ->addRule('moduleexists', Closure::fromCallable([InstalledVersions::class, 'isInstalled']))
            ->addRule('extensionloaded', Closure::fromCallable('extension_loaded'));
    }
}
