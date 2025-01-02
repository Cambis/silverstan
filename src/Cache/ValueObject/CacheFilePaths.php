<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Cache\ValueObject;

final readonly class CacheFilePaths
{
    public function __construct(
        public string $firstDirectory,
        public string $secondDirectory,
        public string $filePath,
    ) {
    }
}
