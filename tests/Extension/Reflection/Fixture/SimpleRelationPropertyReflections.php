<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

// belongs_to
assertType('int', $foo->ParentID);

// has_one
assertType('int', $foo->ChildID);
