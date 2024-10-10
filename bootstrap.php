<?php

declare(strict_types=1);

use Cambis\Silverstan\Application\SilverstanKernel;
use PHPStan\DependencyInjection\Container;
use PHPStan\ShouldNotHappenException;
use SilverStripe\Control\CLIRequestBuilder;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\Connect\NullDatabase;
use SilverStripe\ORM\DB;

if (!$container instanceof Container) {
    throw new ShouldNotHappenException('The autoloader did not receive the container.');
}

/** @var array{includeTestOnly: bool} $silverstanParams */
$silverstanParams = $container->getParameter('silverstan');

// We don't need access to the database
DB::set_conn(new NullDatabase());

// Ensure that the proper globals are set
$globalVars = Environment::getVariables();
$globalVars['_SERVER']['REQUEST_URI'] = '';
$globalVars = CLIRequestBuilder::cleanEnvironment($globalVars);
Environment::setVariables($globalVars);

// Mock a Silverstripe application in order to access the Configuration API
$kernel = new SilverstanKernel(BASE_PATH, $silverstanParams['includeTestOnly']);

$classLoader = $kernel->getClassLoader();

// If Page does not exist, add it!
if (!class_exists('Page')) {
    $classLoader->getManifest()->handleFile(
        __DIR__ . '/stubs',
        __DIR__ . '/stubs/Page.php',
        false
    );

    $classLoader->loadClass('Page');
}

// If PageController does not exist, add it!
if (!class_exists('PageController')) {
    $classLoader->getManifest()->handleFile(
        __DIR__ . '/stubs',
        __DIR__ . '/stubs/PageController.php',
        false
    );

    $classLoader->loadClass('PageController');
}

try {
    $kernel->boot();
} catch (Throwable $e) {
    if (Injector::inst()->has('Psr\Log\LoggerInterface')) {
        Injector::inst()->get('Psr\Log\LoggerInterface')->info($e->getMessage());
    }
}
