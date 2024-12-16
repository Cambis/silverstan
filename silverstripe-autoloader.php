<?php

declare(strict_types=1);

use Cambis\Silverstan\Autoloader\Autoloader;
use PHPStan\DependencyInjection\Container;
use PHPStan\ShouldNotHappenException;

if (!isset($container) || !$container instanceof Container) {
    throw new ShouldNotHappenException('The autoloader did not receive the container.');
}

$autoloader = $container->getByType(Autoloader::class);
$autoloader->register();
