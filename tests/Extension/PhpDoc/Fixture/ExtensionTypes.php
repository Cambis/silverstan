<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Fixture;

use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension\ComplexIntersectionExtension;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension\DNFExtension;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension\SimpleExtension;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension\SimpleIntersectionExtension;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension\UnionExtension;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model\Bar;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model\Foo;
use function PHPStan\Testing\assertType;
use function sprintf;

assertType(Foo::class, (new SimpleExtension())->getOwner());

assertType(
    Bar::class . '|' . Foo::class,
    (new UnionExtension())->getOwner()
);

assertType(
    sprintf('%s&static(%s)', Foo::class, SimpleIntersectionExtension::class),
    (new SimpleIntersectionExtension())->getOwner()
);

assertType(
    sprintf(
        '%s&%s&static(%s)',
        Bar::class,
        Foo::class,
        ComplexIntersectionExtension::class
    ),
    (new ComplexIntersectionExtension())->getOwner()
);

assertType(
    sprintf(
        '(%s&static(%s))|(%s&static(%s))',
        Bar::class,
        DNFExtension::class,
        Foo::class,
        DNFExtension::class
    ),
    (new DNFExtension())->getOwner()
);
