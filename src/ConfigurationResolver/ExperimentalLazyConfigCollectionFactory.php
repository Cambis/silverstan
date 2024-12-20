<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use Cambis\Silverstan\ConfigurationResolver\ConfigCollection\SimpleConfigCollection;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigCollectionFactoryInterface;
use Cambis\Silverstan\ModuleFinder\ModuleFinder;
use Composer\InstalledVersions;
use Override;
use PHPStan\Cache\Cache;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Transformer\PrivateStaticTransformer;
use SilverStripe\Config\Transformer\YamlTransformer;
use function array_keys;
use function class_exists;
use function constant;
use function defined;
use function extension_loaded;
use function sha1;

final readonly class ExperimentalLazyConfigCollectionFactory implements ConfigCollectionFactoryInterface
{
    public function __construct(
        private Cache $cache,
        private ClassManifest $classManifest,
        private ModuleFinder $moduleFinder
    ) {
    }

    #[Override]
    public function create(): ConfigCollectionInterface
    {
        /**
         * @var string[] $config
         * @phpstan-ignore phpstanApi.method
         */
        $config = $this->cache->load(sha1(__FILE__), 'v1') ?? [];

        $collection = (new SimpleConfigCollection($config))
            ->transform([
                $this->getPrivateStaticTransformer(),
                $this->getYamlTransformer(),
            ]);

        /** @phpstan-ignore phpstanApi.method */
        $this->cache->save(sha1(__FILE__), 'v1', $collection->getAll());

        return $collection;
    }

    private function getYamlTransformer(): YamlTransformer
    {
        return YamlTransformer::create(
            $this->moduleFinder->getAppRootDirectory(),
            $this->moduleFinder->getYamlConfigFiles()
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

    private function getPrivateStaticTransformer(): PrivateStaticTransformer
    {
        return new PrivateStaticTransformer(
            array_keys($this->classManifest->classMap->getMap())
        );
    }
}
