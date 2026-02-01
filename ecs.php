<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/e2e',
    ])
    ->withConfiguredRule(
        NewWithParenthesesFixer::class,
        [
            'anonymous_class' => false,
        ]
    )
    ->withConfiguredRule(
        ReferenceUsedNamesOnlySniff::class,
        [
            'allowFallbackGlobalFunctions' => false,
            'allowFallbackGlobalConstants' => false,
        ]
    )
    ->withConfiguredRule(
        OrderedImportsFixer::class,
        [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ]
    )
    ->withPreparedSets(
        common: true,
        psr12: true,
    )
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class,
        ReferenceUsedNamesOnlySniff::class => [
            __DIR__ . '/bootstrap.php',
            __DIR__ . '/stubs/Page.php',
            __DIR__ . '/stubs/PageController.php',
        ],
    ]);
