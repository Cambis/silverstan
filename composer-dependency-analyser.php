<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->addPathToScan(__DIR__ . '/bootstrap.php', false)
    ->ignoreUnknownFunctions(['PHPStan\Testing\assertType'])
    ->ignoreUnknownClasses(['Page', 'PageController'])
    ->ignoreErrorsOnPackagesAndPaths(
        [
            // Bundled with phpstan/phpstan
            'nikic/php-parser',
            'phpstan/phpdoc-parser',
            'symfony/finder',
        ],
        [__DIR__ . '/src'],
        [ErrorType::SHADOW_DEPENDENCY]
    )
    ->ignoreErrorsOnPackagesAndPaths(
        [
            // Bundled with phpstan/phpstan
            'nikic/php-parser',
        ],
        [__DIR__ . '/tests'],
        [ErrorType::SHADOW_DEPENDENCY]
    )
    ->ignoreErrorsOnPackagesAndPaths(
        [
            'silverstripe/framework',
        ],
        [
            __DIR__ . '/bootstrap.php',
            __DIR__ . '/src/Application/SilverstanKernel.php',
            __DIR__ . '/src/ConfigurationResolver/LazyConfigCollectionFactory.php',
        ],
        [ErrorType::DEV_DEPENDENCY_IN_PROD]
    );
