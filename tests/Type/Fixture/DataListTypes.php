<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use SilverStripe\Assets\File;
use SilverStripe\ORM\DataList;
use function PHPStan\Testing\assertType;

assertType(
    DataList::class . '<' . File::class . '>',
    File::get()
);

assertType(
    File::class . '|null',
    File::get()->first()
);

assertType(
    File::class . '|null',
    File::get()->last()
);
