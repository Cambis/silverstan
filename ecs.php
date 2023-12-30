<?php

declare(strict_types=1);

use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/rector.php',
        __DIR__ . '/src',
        __DIR__ . '/stubs',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->ruleWithConfiguration(
        ReferenceUsedNamesOnlySniff::class,
        [
            'allowFallbackGlobalFunctions' => false,
            'allowFallbackGlobalConstants' => false,
        ]
    );

    $ecsConfig->ruleWithConfiguration(
        UseSpacingSniff::class,
        [
            'linesCountBetweenUseTypes' => 1,
        ]
    );

    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::CLEAN_CODE,
        SetList::PSR_12,
    ]);

    $ecsConfig->skip([
        '*/Fixture/*',
        '*/Source/*',
    ]);
};
