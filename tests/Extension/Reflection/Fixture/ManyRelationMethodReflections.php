<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Bar;
use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use SilverStripe\ORM\HasManyList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use function PHPStan\Testing\assertType;
use function sprintf;

$foo = Foo::create();
$bar = Bar::create();

// belongs_many_many
assertType(
    sprintf('%s<%s>', ManyManyList::class, Foo::class),
    $foo->Ancestors()
);

// has_many
assertType(
    sprintf('%s<%s>', HasManyList::class, Foo::class),
    $foo->Siblings()
);

assertType(
    sprintf('%s<%s>', HasManyList::class, Foo::class),
    $foo->TypehintedHasMany()
);

// many_many
assertType(
    sprintf('%s<%s>', ManyManyList::class, Foo::class),
    $foo->Family()
);

// many_many_through
assertType(
    sprintf('%s<%s>', ManyManyThroughList::class, Foo::class),
    $foo->ExtendedFamily()
);

// Mult-relational has_one
assertType(
    sprintf('%s<%s>', HasManyList::class, Foo::class),
    $bar->CaregiversOne()
);

assertType(
    sprintf('%s<%s>', HasManyList::class, Foo::class),
    $bar->CaregiversTwo()
);
