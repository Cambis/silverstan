<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreUnknownFunctions(['PHPStan\Testing\assertType'])
    ->ignoreErrorsOnPackagesAndPaths(
        [
            // These are bundled with phpstan/phpstan
            'nikic/php-parser',
            'phpstan/phpdoc-parser',
            'symfony/finder',
        ],
        [
            __DIR__ . '/src',
        ],
        [ErrorType::SHADOW_DEPENDENCY]
    )
    ->ignoreErrorsOnPackagesAndPaths(
        [
            // These are bundled with phpstan/phpstan
            'nikic/php-parser',
        ],
        [
            __DIR__ . '/tests',
        ],
        [ErrorType::SHADOW_DEPENDENCY]
    );
