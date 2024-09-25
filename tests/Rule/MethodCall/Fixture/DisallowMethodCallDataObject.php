<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Source\Model\Foo;

$foo = Foo::create();

$foo->Bar()->doSomethingPotentiallyDangerous();

if ($foo->Bar()->exists()) {
    $foo->Bar()->doSomethingPotentiallyDangerous();
}

if (!$foo->Bar()->exists()) {
    $foo->Bar()->doSomethingPotentiallyDangerous();
}

$foo->Bar()->write();
$foo->Bar()->doSomethingPotentiallyDangerous();

$foo->Bar()->delete();
$foo->Bar()->doSomethingPotentiallyDangerous();

$foo->Baz()->doSomethingPotentiallyDangerous();

$foo->Bar()->doSomethingSafe();
