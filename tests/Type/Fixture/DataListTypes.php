<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use Cambis\Silverstan\Tests\Type\Source\Model\Foo;
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
