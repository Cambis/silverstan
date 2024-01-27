<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use SilverStripe\Assets\File;
use SilverStripe\ORM\ArrayList;

use function PHPStan\Testing\assertType;

$list = new ArrayList([File::create(['Name' => 'Foo'])]);

assertType(
    ArrayList::class . '<' . File::class . '>',
    $list
);

assertType(
    File::class . '|null',
    $list->first()
);

assertType(
    File::class . '|null',
    $list->last()
);

foreach ($list as $item) {
    assertType(File::class, $item);
}
