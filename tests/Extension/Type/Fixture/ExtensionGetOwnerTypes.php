<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Fixture;

use Cambis\Silverstan\Tests\Extension\Type\Source\Extension\BarExtension;
use Cambis\Silverstan\Tests\Extension\Type\Source\Model\Bar;
use function PHPStan\Testing\assertType;
use function sprintf;

assertType(
    sprintf('%s&static(%s)', Bar::class, BarExtension::class),
    (new BarExtension())->getOwner()
);
