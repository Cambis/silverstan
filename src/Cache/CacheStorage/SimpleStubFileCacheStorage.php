<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Cache\CacheStorage;

use Cambis\Silverstan\Cache\ValueObject\CacheFilePaths;
use Override;
use PHPStan\Cache\CacheStorage;
use PHPStan\ShouldNotHappenException;
use function clearstatcache;
use function error_get_last;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function sha1;
use function sprintf;
use function substr;

/**
 * Simple file cache storage that stores items as stub files.
 *
 * Inspired by https://github.com/bitExpert/phpstan-magento/blob/master/src/bitExpert/PHPStan/Magento/Autoload/Cache/FileCacheStorage.php.
 *
 * @phpstan-ignore phpstanApi.interface
 */
final readonly class SimpleStubFileCacheStorage implements CacheStorage
{
    public function __construct(
        private string $directory
    ) {
    }

    /**
     * @return ?string
     */
    #[Override]
    public function load(string $key, string $variableKey)
    {
        $filePath = $this->getFilePaths($key)->filePath;

        return (static function () use ($filePath): ?string {
            if (!is_file($filePath)) {
                return null;
            }

            return $filePath;
        })();
    }

    #[Override]
    public function save(string $key, string $variableKey, $data): void
    {
        $cacheFilePaths = $this->getFilePaths($key);

        $this->makeDir($cacheFilePaths->firstDirectory);
        $this->makeDir($cacheFilePaths->secondDirectory);

        $result = @file_put_contents($cacheFilePaths->filePath, $data);

        if ($result !== false) {
            return;
        }

        throw new ShouldNotHappenException(
            sprintf('Could not write data to cache file %s.', $cacheFilePaths->filePath)
        );
    }

    private function getFilePaths(string $key): CacheFilePaths
    {
        $keyHash = sha1($key);
        $firstDirectory = sprintf('%s/%s', $this->directory, substr($keyHash, 0, 2));
        $secondDirectory = sprintf('%s/%s', $firstDirectory, substr($keyHash, 2, 2));
        $filePath = sprintf('%s/%s.stub', $secondDirectory, $keyHash);

        return new CacheFilePaths($firstDirectory, $secondDirectory, $filePath);
    }

    private function makeDir(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        $result = @mkdir($directory, 0777, true);

        if ($result) {
            return;
        }

        clearstatcache();

        if (is_dir($directory)) {
            return;
        }

        $error = error_get_last() ?? [
            'message' => 'unknown',
        ];

        throw new ShouldNotHappenException(
            sprintf(
                'Failed to create directory %s (%s).',
                $this->directory,
                $error['message']
            )
        );
    }
}
