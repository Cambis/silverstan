<?php

namespace Cambis\Silverstan\Tests\Rule\StaticCall\Fixture;

use Cambis\Silverstan\Tests\Rule\StaticCall\Source\Foo;

Foo::create('bar', 1);

Foo::create();

Foo::create(1, 2);

Foo::create(param1: 2);
