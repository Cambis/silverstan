<?php

namespace Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Fixture;

use Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Source\Extension\FooExtension;
use Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Source\Model\Foo;
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
