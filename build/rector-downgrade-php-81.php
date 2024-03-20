<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;

return RectorConfig::configure()
    ->withImportNames(removeUnusedImports: true)
    ->withSets([DowngradeLevelSetList::DOWN_TO_PHP_81]);
