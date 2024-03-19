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
    public function doSomething(): void
    {
        assertType(
            File::class . '|null',
            $this->File()
        );
    }
}

assertType(
    File::class . '|null',
    DataObjectTypes::create()->File()
);
