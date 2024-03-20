<?php

declare(strict_types=1);

use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withConfiguredRule(
        ReferenceUsedNamesOnlySniff::class,
        [
            'allowFallbackGlobalFunctions' => false,
            'allowFallbackGlobalConstants' => false,
        ]
    )->withConfiguredRule(
        UseSpacingSniff::class,
        [
            'linesCountBetweenUseTypes' => 1,
        ]
    )
    ->withPreparedSets(
        arrays: true,
        cleanCode: true,
        psr12: true,
    );
