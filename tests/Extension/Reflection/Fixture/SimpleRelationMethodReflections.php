<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

// belongs_to
assertType(Foo::class, $foo->Parent());

// has_one
assertType(Foo::class, $foo->Child());
assertType(Foo::class, $foo->TypehintedHasOne());
