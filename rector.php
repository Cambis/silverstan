<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withImportNames(removeUnusedImports: true)
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(
        php83: true
    )
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        deadCode: true,
        earlyReturn: true,
        privatization: true
    )
    ->withSets([
        PHPUnitSetList::PHPUNIT_90,
    ])
    ->withSkip([
        '*/Fixture/*',
        '*/Source/*',
        AddTypeToConstRector::class,
        ClosureToArrowFunctionRector::class,
        UseClassKeywordForClassNameResolutionRector::class => [
            __DIR__ . '/tests',
        ]
    ]);
