<?php

namespace Cambis\Silverstan\Tests\Extension\MethodCall\Fixture;

use SilverStripe\Assets\File;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

use function PHPStan\Testing\assertType;

/**
 * @method File File()
 */
final class DataObjectTypes extends DataObject implements TestOnly
{
}

$file = DataObjectTypes::create()->File();

assertType(
    'mixed',
    $file
);
