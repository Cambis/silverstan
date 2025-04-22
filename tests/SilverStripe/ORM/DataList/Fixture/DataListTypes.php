<?php

namespace Cambis\Silverstan\Tests\SilverStripe\ORM\DataList\Fixture;

use Cambis\Silverstan\Tests\SilverStripe\ORM\DataList\Source\Model\Foo;
use SilverStripe\ORM\DataList;
use function PHPStan\Testing\assertType;

assertType(
    DataList::class . '<' . Foo::class . '>',
    Foo::get()
);

assertType(
    Foo::class . '|null',
    Foo::get()->first()
);

assertType(
    Foo::class . '|null',
    Foo::get()->last()
);
