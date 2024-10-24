<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType(Foo::class, $foo->diObject);
assertType('array<int, mixed>', $foo->diArray);
assertType('array<' . Foo::class . '>', $foo->typedDiArray);
