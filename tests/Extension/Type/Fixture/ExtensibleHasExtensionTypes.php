<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Fixture;

use Cambis\Silverstan\Tests\Extension\Type\Source\Extension\FooExtension;
use Cambis\Silverstan\Tests\Extension\Type\Source\Model\Foo;
use function PHPStan\Testing\assertType;
use function sprintf;

$foo = Foo::create();

if ($foo->hasExtension(FooExtension::class)) {
    assertType(
        sprintf('%s&%s', FooExtension::class, Foo::class),
        $foo
    );

    assertType('bool', $foo->doSomething());
}
