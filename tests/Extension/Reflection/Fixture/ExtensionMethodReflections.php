<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType('bool', $foo->publicMethod(true));
assertType('bool', $foo->protectedMethod(true));
assertType('*ERROR*', $foo->privateMethod(true));
