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

/**
 * @deprecated since 1.0.0
 */

if (!isset($container) || !$container instanceof Container) {
    throw new ShouldNotHappenException('The autoloader did not receive the container.');
}

/** @var string[] $bootstrapFiles */
$bootstrapFiles = $container->getParameter('bootstrapFiles');

// Use the new bleedingEdge autoloader
if (in_array(__DIR__ . '/silverstripe-autoloader.php', $bootstrapFiles)) {
    return;
}

foreach ($bootstrapFiles as $bootstrapFile) {
    if (!str_contains($bootstrapFile, 'cambis/silverstan/silverstripe-autoloader.php')) {
        continue;
    }

    return;
}

// Don't continue if there is no Silverstripe installation
if (!class_exists('SilverStripe\Core\Config\Config')) {
    throw new ShouldNotHappenException("\n\nCould not find `silverstripe/framework`, did you forget to install?\n");
}

/** @var array{includeTestOnly: bool} $silverstanParams */
$silverstanParams = $container->getParameter('silverstan');

// We don't need access to the database
DB::set_conn(new NullDatabase(), 'default');

// Ensure that the proper globals are set
$globalVars = Environment::getVariables();
$globalVars['_SERVER']['REQUEST_URI'] = '';
$globalVars = CLIRequestBuilder::cleanEnvironment($globalVars);
Environment::setVariables($globalVars);

// Mock a Silverstripe application in order to access the Configuration API
$kernel = new SilverstanKernel(BASE_PATH, $silverstanParams['includeTestOnly']);

// Preemptively generate the class manifest, so we can check for the existence of Page and PageController
$classLoader = $kernel->getClassLoader();
$classManifest = $classLoader->getManifest();
$classManifest->init($silverstanParams['includeTestOnly'], false);

// If Page does not exist, add it!
if (!array_key_exists('page', $classManifest->getClassNames())) {
    $classManifest->handleFile(
        __DIR__ . '/stubs',
        __DIR__ . '/stubs/Page.php',
        false
    );

    $classLoader->loadClass('Page');
}

// If PageController does not exist, add it!
if (!array_key_exists('pagecontroller', $classManifest->getClassNames())) {
    $classManifest->handleFile(
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
