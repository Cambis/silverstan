<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Fixture;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType('bool', $foo->publicMethod(true));
assertType('bool', $foo->PublicMethod(true)); // Wrong case but still valid PHP
assertType('*ERROR*', $foo->protectedMethod(true));
assertType('*ERROR*', $foo->privateMethod(true));
assertType('*ERROR*', $foo::publicStaticMethod(true));

assertType('Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo', $foo->ExtensionHasOne());
assertType('Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo', $foo->extensionHasOne()); // Wrong case but still valid PHP
