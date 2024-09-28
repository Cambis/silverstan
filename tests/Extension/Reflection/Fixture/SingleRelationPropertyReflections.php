<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType('bool', $foo->Boolean);
assertType('float', $foo->Currency);
assertType('string|null', $foo->Date);
assertType('float', $foo->Decimal);
assertType('string|null', $foo->Enum);
assertType('string|null', $foo->HTMLText);
assertType('string|null', $foo->HTMLVarchar);
assertType('int', $foo->Int);
assertType('float', $foo->Percentage);
assertType('string|null', $foo->Datetime);
assertType('string|null', $foo->Text);
assertType('string|null', $foo->Time);
assertType('string|null', $foo->Varchar);
assertType('string', $foo->RequiredField);

// From extension
assertType('string|null', $foo->ExtensionField);

// belongs_to
assertType('int', $foo->ParentID);

// has_one
assertType('int', $foo->ChildID);
