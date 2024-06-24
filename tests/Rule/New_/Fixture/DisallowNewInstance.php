<?php

namespace Cambis\Silverstan\Tests\Rule\Variable\Fixture;

use Cambis\Silverstan\Tests\Rule\New_\Source\InjectableClass;
use Cambis\Silverstan\Tests\Rule\New_\Source\NonInjectableClass;
use SilverStripe\Dev\TestOnly;

final class DisallowNewInstance implements TestOnly
{
    public function doSomething(): void
    {
        $foo = new InjectableClass();
        $bar = new NonInjectableClass();
        $baz = InjectableClass::create();
    }
}
