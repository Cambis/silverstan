<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Analyser\ResultCache\ResultCacheMetaExtension;

use Cambis\Silverstan\FileFinder\FileFinder;
use Override;
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

    #[Override]
    public function getKey(): string
    {
        return 'v1-config-version';
    }

    #[Override]
    public function getHash(): string
    {
        return $this->fileFinder->getYamlConfigCacheKey();
    }
}
