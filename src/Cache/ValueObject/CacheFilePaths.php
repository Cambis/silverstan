<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Cache\ValueObject;

final class CacheFilePaths
{
    /**
     * @readonly
     */
    public string $firstDirectory;
    /**
     * @readonly
     */
    public string $secondDirectory;
    /**
     * @readonly
     */
    public string $filePath;
    public function __construct(string $firstDirectory, string $secondDirectory, string $filePath)
    {
        $this->firstDirectory = $firstDirectory;
        $this->secondDirectory = $secondDirectory;
        $this->filePath = $filePath;
    }
}
