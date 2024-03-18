<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use SilverStripe\Assets\File;
use SilverStripe\Core\Injector\Injector;

use function PHPStan\Testing\assertType;
use function singleton;

assertType(
    File::class,
    Injector::inst()->get(File::class)
);

assertType(
    File::class,
    singleton(File::class)
);

assertType(
    File::class,
    Injector::inst()->create(File::class)
);

assertType(
    File::class,
    Injector::inst()->createWithArgs(File::class, [
        'Name' => 'Foo',
    ])
);
