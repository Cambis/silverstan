<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\DBField\FooField;
use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Bar;
use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use function PHPStan\Testing\assertType;
use function sprintf;

$foo = Foo::create();
$bar = Bar::create();

// Fixed fields
assertType('int', $foo->ID);
assertType('int', $foo->OldID);
assertType('string', $foo->Title);
assertType(sprintf('class-string<%s>', Foo::class), $foo->ClassName);
assertType('string', $foo->LastEdited);
assertType('string', $foo->Created);
assertType(sprintf('class-string<%s>|null', Foo::class), $foo->ObsoleteClassName);

// Basic fields
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
assertType('string', $foo->TypehintedField);

// Inherited fields
assertType('bool', $bar->Boolean);
assertType('float', $bar->Currency);
assertType('string|null', $bar->Date);
assertType('float', $bar->Decimal);
assertType('string|null', $bar->Enum);
assertType('string|null', $bar->HTMLText);
assertType('string|null', $bar->HTMLVarchar);
assertType('int', $bar->Int);
assertType('float', $bar->Percentage);
assertType('string|null', $bar->Datetime);
assertType('string|null', $bar->Text);
assertType('string|null', $bar->Time);
assertType('string|null', $bar->Varchar);
assertType('string', $bar->TypehintedField);

// Default value fields
assertType('bool', $foo->BooleanDefaultFalseWithSpace);

// Malformed fields
assertType('*ERROR*', $foo->MalformedField);

// Fields with getters
assertType('string', $foo->FieldWithGetter);
assertType('non-empty-string', $foo->FieldWithDefaultValueGetter);

// Custom db fields
assertType(FooField::class, $foo->FooField);
