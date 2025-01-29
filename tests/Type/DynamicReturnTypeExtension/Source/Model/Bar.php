<?php

namespace Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Source\Model;

use Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Source\Extension\BarExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class Bar extends DataObject implements TestOnly
{
    private static array $extensions = [
        BarExtension::class,
    ];
}
