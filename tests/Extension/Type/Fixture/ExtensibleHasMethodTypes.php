<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Fixture;

use Cambis\Silverstan\Tests\Extension\Type\Source\Model\Foo;
use function PHPStan\Testing\assertType;
use function sprintf;

$foo = Foo::create();

if ($foo->hasMethod('doSomething')) {
    assertType(
        sprintf('%s&hasMethod(%s)', Foo::class, 'doSomething'),
        $foo
    );

    $foo->doSomething();
}
