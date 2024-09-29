<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType('bool', $foo->publicMethod());
assertType('bool', $foo->protectedMethod());
assertType('*ERROR*', $foo->privateMethod());
