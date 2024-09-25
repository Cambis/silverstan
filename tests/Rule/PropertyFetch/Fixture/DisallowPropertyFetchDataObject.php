<?php

namespace Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture;

use Cambis\Silverstan\Tests\Rule\PropertyFetch\Source\Model\Foo;

$foo = Foo::create();

$foo->Bar()->Title;
$foo->Bar()->Title = 'Foo';

if ($foo->Bar()->exists()) {
    $foo->Bar()->Title;
}

if (!$foo->Bar()->exists()) {
    $foo->Bar()->Title;
}

$foo->Bar()->write();
$foo->Bar()->Title;

$foo->Bar()->delete();
$foo->Bar()->Title;

$foo->Baz()->Title;
