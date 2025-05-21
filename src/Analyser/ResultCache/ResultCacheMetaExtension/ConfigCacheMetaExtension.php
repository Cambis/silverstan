<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Analyser\ResultCache\ResultCacheMetaExtension;

use Cambis\Silverstan\FileFinder\FileFinder;
use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;

final class ConfigCacheMetaExtension implements ResultCacheMetaExtension
{
    /**
     * @readonly
     */
    private FileFinder $fileFinder;
    public function __construct(FileFinder $fileFinder)
    {
        $this->fileFinder = $fileFinder;
    }

    public function getKey(): string
    {
        return 'v1-config-version';
    }

    public function getHash(): string
    {
        return $this->fileFinder->getYamlConfigCacheKey();
    }
}
