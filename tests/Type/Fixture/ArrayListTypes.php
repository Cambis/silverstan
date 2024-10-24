<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use Cambis\Silverstan\Tests\Type\Source\Model\Foo;
use SilverStripe\ORM\ArrayList;
use function PHPStan\Testing\assertType;

$list = new ArrayList([
    Foo::create([
        'Name' => 'Foo',
    ])]);

assertType(
    ArrayList::class . '<' . Foo::class . '>',
    $list
);

assertType(
    Foo::class . '|null',
    $list->first()
);

assertType(
    Foo::class . '|null',
    $list->last()
);

foreach ($list as $item) {
    assertType(Foo::class, $item);
}
