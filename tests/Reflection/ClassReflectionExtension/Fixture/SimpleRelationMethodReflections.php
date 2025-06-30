<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Fixture;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

// belongs_to
assertType(Foo::class, $foo->Parent());
assertType(Foo::class, $foo->parent()); // Wrong case but still valid PHP

// has_one
assertType(Foo::class, $foo->Child());
assertType(Foo::class, $foo->TypehintedHasOne());
