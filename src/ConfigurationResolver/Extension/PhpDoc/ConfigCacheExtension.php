<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Extension\PhpDoc;

use Cambis\Silverstan\FileFinder\FileFinder;
use PHPStan\Cache\Cache;
use PHPStan\PhpDoc\StubFilesExtension;
use function sprintf;

/**
 * This extension will invalidate PHPStan's result cache when the Silverstripe config is updated.
 *
 * @todo this can likely be replaced once the following issue is resolved: https://github.com/phpstan/phpstan-symfony/issues/255.
 */
final class ConfigCacheExtension implements StubFilesExtension
{
    /**
     * @readonly
     */
    private Cache $cache;
    /**
     * @var string
     */
    private const CACHE_VERSION = 'v1-config-version';

    /**
     * @readonly
     */
    private string $cacheKey;

    public function __construct(
        Cache $cache,
        FileFinder $fileFinder
    ) {
        $this->cache = $cache;
        $this->cacheKey = $fileFinder->getYamlConfigCacheKey();
    }

    public function getFiles(): array
    {
        /**
         * @var ?string
         * @phpstan-ignore phpstanApi.method
         */
        $path = $this->cache->load($this->cacheKey, self::CACHE_VERSION);
        if ($path === null) {
            /** @phpstan-ignore phpstanApi.method */
            $this->cache->save(
                $this->cacheKey,
                self::CACHE_VERSION,
                sprintf("<?php\n\ndeclare(strict_types = 1);\n\n/** AUTOGENERATED %s */\n", $this->cacheKey)
            );

            /**
             * @var string
             * @phpstan-ignore phpstanApi.method
             */
            $path = $this->cache->load($this->cacheKey, self::CACHE_VERSION);
        }
        return [$path];
    }
}
