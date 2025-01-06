<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Source\Model;

use Cambis\Silverstan\Tests\Extension\Type\Source\Extension\BarExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class Bar extends DataObject implements TestOnly
{
    private static array $extensions = [
        BarExtension::class,
    ];
}
