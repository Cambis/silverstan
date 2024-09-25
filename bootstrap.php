<?php

declare(strict_types=1);

use SilverStripe\Control\CLIRequestBuilder;
use SilverStripe\Core\DatabaselessKernel;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\Connect\NullDatabase;
use SilverStripe\ORM\DB;

// Add Page/PageController stubs which may be required
if (!class_exists(Page::class)) {
    require __DIR__ . '/stubs/Page.php';
}

if (!class_exists(PageController::class)) {
    require __DIR__ . '/stubs/PageController.php';
}

// We don't need access to the database
DB::set_conn(new NullDatabase());

// Ensure that the proper globals are set
$globalVars = Environment::getVariables();
$globalVars['_SERVER']['REQUEST_URI'] = '';
$globalVars = CLIRequestBuilder::cleanEnvironment($globalVars);
Environment::setVariables($globalVars);

// Mock a Silverstripe application in order to access the Configuration API
try {
    $kernel = new class(BASE_PATH) extends DatabaselessKernel {
        protected function getIncludeTests()
        {
            // Only include `\SilverStripe\Dev\TestOnly` if we are running PHPUnit
            return defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
        }
    };

    $kernel->boot();
} catch (Throwable $e) {
    echo $e->getMessage();
}
