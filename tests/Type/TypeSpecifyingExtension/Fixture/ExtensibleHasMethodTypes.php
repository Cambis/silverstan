<?php

namespace Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Fixture;

use Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Source\Model\Foo;
use function PHPStan\Testing\assertType;
use function sprintf;

$foo = Foo::create();

if ($foo->hasMethod('doSomething')) {
    assertType(
        sprintf('%s&hasMethod(%s)', Foo::class, 'doSomething'),
        $foo
    );

    assertType('mixed', $foo->doSomething());
}
