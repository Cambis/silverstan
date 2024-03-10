<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Fixture;

use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Extension;

use function PHPStan\Testing\assertType;

class Foo extends DataObject
{
    public function foo(): string
    {
        return 'foo';
    }
}

class Bar extends DataObject
{
    public function bar(): bool
    {
        return true;
    }
}

/**
 * @extends Extension<Foo>
 */
class ObjectExtension extends Extension
{
    public function baz(): void
    {
        assertType(
            Foo::class,
            $this->getOwner()
        );
    }
}

/**
 * @extends Extension<Foo|Bar>
 */
class UnionExtension extends Extension
{
    public function baz(): void
    {
        assertType(
            Bar::class . '|' . Foo::class,
            $this->getOwner()
        );
    }
}

/**
 * @extends Extension<Foo&static>
 */
class IntersectionExtension extends Extension
{
    public function baz(): void
    {
        assertType(
            Foo::class,
            $this->getOwner()
        );
    }
}

/**
 * @extends Extension<(Foo&static)|(Bar&static)>
 */
class DNFExtension extends Extension
{
    public function baz(): void
    {
        assertType(
            Bar::class . '|' . Foo::class,
            $this->getOwner()
        );
    }
}
