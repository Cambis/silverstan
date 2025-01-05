<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\ConfigCollection;

use Cambis\Silverstan\FileFinder\FileFinder;
use Override;
use PHPStan\Cache\Cache;
use SilverStripe\Config\Collections\MutableConfigCollectionInterface;
use SilverStripe\Config\MergeStrategy\Priority;
use SilverStripe\Config\Middleware\MiddlewareAware;
use SilverStripe\Config\Transformer\TransformerInterface;
use function array_key_exists;
use function is_array;
use function strtolower;

/**
 * Cached collection that uses PHPStan's inbuilt cache.
 *
 * Methods from `SilverStripe\Config\Collections\MutableConfigCollectionInterface` are not strongly typed so they are backwards compatible.
 */
final class CachedConfigCollection implements MutableConfigCollectionInterface
{
    use MiddlewareAware;

    /**
     * @var string
     */
    private const CACHE_VERSION = 'v1-config';

    private readonly string $cacheKey;

    /**
     * @var array<string, array<bool|int, mixed[]>>
     */
    private array $callCache = [];

    public function __construct(
        private readonly Cache $cache,
        FileFinder $fileFinder,
    ) {
        $this->cacheKey = $fileFinder->getConfigCacheKey();
    }

    /**
     * @return ($name is null ? mixed[] : mixed)
     */
    #[Override]
    public function get($class, $name = null, $excludeMiddleware = 0): mixed
    {
        $config = $this->getClassConfig($class, $excludeMiddleware);

        if ($name === null) {
            return $config;
        }

        if (!array_key_exists($name, $config)) {
            return null;
        }

        return $config[$name];
    }

    #[Override]
    public function exists($class, $name = null, $excludeMiddleware = 0): bool
    {
        $config = $this->get($class, null, $excludeMiddleware);

        if ($config === []) {
            return false;
        }

        if ($name === null) {
            return true;
        }

        return array_key_exists($name, $config);
    }

    /**
     * @param string $class
     * @param ?string $name
     * @param mixed $value
     * @param mixed[] $metadata
     */
    #[Override]
    public function set($class, $name, $value, $metadata = []): static
    {
        $config = $this->getAll();
        $classKey = strtolower($class);

        if ($name === null) {
            $config[$classKey] = $value;
        } else {
            $config[$classKey][$name] = $value;
        }

        /** @phpstan-ignore phpstanApi.method */
        $this->cache->save($this->cacheKey, self::CACHE_VERSION, $config);

        $this->callCache = [];

        return $this;
    }

    /**
     * @param string $class
     * @param string $name
     * @param mixed[] $value
     */
    #[Override]
    public function merge($class, $name, $value): static
    {
        $existing = $this->get($class, $name, true);

        if (is_array($existing)) {
            $value = Priority::mergeArray($value, $existing);
        }

        $this->set($class, $name, $value);

        return $this;
    }

    /**
     * @param string $class
     * @param ?string $name
     */
    #[Override]
    public function remove($class, $name = null): static
    {
        $config = $this->getAll();
        $classKey = strtolower($class);

        if ($name === null) {
            unset($config[$classKey]);
        } else {
            unset($config[$classKey][$name]);
        }

        /** @phpstan-ignore phpstanApi.method */
        $this->cache->save($this->cacheKey, self::CACHE_VERSION, $config);

        unset($this->callCache[$classKey]);

        return $this;
    }

    #[Override]
    public function removeAll(): void
    {
        /** @phpstan-ignore phpstanApi.method */
        $this->cache->save($this->cacheKey, self::CACHE_VERSION, []);

        $this->callCache = [];
    }

    /**
     * Trigger transformers to load into this store
     *
     * @param list<TransformerInterface> $transformers
     * @api
     */
    public function transform(array $transformers): static
    {
        foreach ($transformers as $transformer) {
            $transformer->transform($this);
        }

        return $this;
    }

    #[Override]
    public function getMetadata(): array
    {
        return [];
    }

    #[Override]
    public function getHistory(): array
    {
        return [];
    }

    #[Override]
    public function nest(): static
    {
        return $this;
    }

    /**
     * @return array<string, mixed[]>
     */
    #[Override]
    public function getAll(): array
    {
        /**
         * @var array<string, mixed[]>
         * @phpstan-ignore phpstanApi.method
         */
        $config = $this->cache->load($this->cacheKey, self::CACHE_VERSION) ?? [];

        return $config;
    }

    /**
     * @return mixed[]
     */
    private function getClassConfig(string $class, bool|int $excludeMiddleware = 0): array
    {
        $config = $this->getAll();
        $classKey = strtolower($class);

        if ($excludeMiddleware === true) {
            return $config[$classKey] ?? [];
        }

        // Check cache
        if (isset($this->callCache[$classKey][$excludeMiddleware])) {
            return $this->callCache[$classKey][$excludeMiddleware];
        }

        // Build middleware
        $result = $this->callMiddleware(
            $class,
            $excludeMiddleware,
            function ($class, $excludeMiddleware): array {
                return $this->getClassConfig($class, true);
            }
        );

        // Save cache
        if (!isset($this->callCache[$classKey])) {
            $this->callCache[$classKey] = [];
        }

        $this->callCache[$classKey][$excludeMiddleware] = $result;

        return $result;
    }
}
