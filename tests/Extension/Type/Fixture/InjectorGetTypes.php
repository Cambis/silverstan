<?php

namespace Cambis\Silverstan\Tests\Type\Fixture;

use Cambis\Silverstan\Tests\Type\Source\Model\Foo;
use SilverStripe\Core\Injector\Injector;
use function PHPStan\Testing\assertType;
use function singleton;

assertType(
    Foo::class,
    Injector::inst()->get('Foo')
);

assertType(
    Foo::class,
    Injector::inst()->get(Foo::class)
);

assertType(
    Foo::class,
    singleton('Foo')
);

assertType(
    Foo::class,
    singleton(Foo::class)
);

assertType(
    Foo::class,
    Injector::inst()->create(Foo::class)
);

assertType(
    Foo::class,
    Injector::inst()->createWithArgs(Foo::class, [
        'Title' => 'Bar',
    ])
);
