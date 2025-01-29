<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Fixture;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

// belongs_to
assertType('int', $foo->ParentID);

// has_one
assertType('int', $foo->ChildID);
