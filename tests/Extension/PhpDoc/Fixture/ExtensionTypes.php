<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Fixture;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

use function PHPStan\Testing\assertType;

class Foo extends DataObject implements TestOnly
{
    public function foo(): string
    {
        return 'foo';
    }
}

class Bar extends DataObject implements TestOnly
{
    public function bar(): bool
    {
        return true;
    }
}

/**
 * @extends Extension<Foo>
 */
class ObjectExtension extends Extension implements TestOnly
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
class UnionExtension extends Extension implements TestOnly
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
class IntersectionExtension extends Extension implements TestOnly
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
class DNFExtension extends Extension implements TestOnly
{
    public function baz(): void
    {
        assertType(
            Bar::class . '|' . Foo::class,
            $this->getOwner()
        );
    }
}
