<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Fixture;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType(Foo::class, $foo->diObject);
assertType('array<int, mixed>', $foo->diArray);
assertType('array<' . Foo::class . '>', $foo->typedDiArray);
