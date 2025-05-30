<?php

namespace Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Fixture;

use Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Source\Extension\BarExtension;
use Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Source\Model\Bar;
use function PHPStan\Testing\assertType;
use function sprintf;

assertType(
    sprintf('%s&static(%s)', Bar::class, BarExtension::class),
    (new BarExtension())->getOwner()
);
