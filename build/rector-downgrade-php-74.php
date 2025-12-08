<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;

return RectorConfig::configure()
    ->withImportNames(removeUnusedImports: true)
    ->withDowngradeSets(php74: true)
    ->withSkip([
        // TODO: Weird edge case malforms DisallowMethodCallOnUnsafeDataObjectRule::DEFAULT_ALLOWED_METHOD_CALLS.
        // Skip everywhere incase it affects other classes.
        DowngradeArraySpreadRector::class,
    ]);
