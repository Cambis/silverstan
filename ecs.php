<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
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
        GlobalNamespaceImportFixer::class,
        [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
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
