<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType('bool', $foo->publicMethod(true));
assertType('*ERROR*', $foo->protectedMethod(true));
assertType('*ERROR*', $foo->privateMethod(true));
assertType('*ERROR*', $foo::publicStaticMethod(true));

assertType('Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo', $foo->ExtensionHasOne());
