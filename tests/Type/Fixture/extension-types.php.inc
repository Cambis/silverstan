<?php

namespace Cambis\Silverstan\Type\Tests\Fixture;

use SilverStripe\ORM\DataExtension;
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
            Foo::class . '|static(' . self::class . ')',
            $this->getOwner()
        );
    }
}

/**
 * @extends DataExtension<Foo>
 */
class DataObjectExtension extends DataExtension
{
    public function baz(): void
    {
        assertType(
            Foo::class . '|static(' . self::class . ')',
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
            Bar::class . '|' . Foo::class . '|static(' . self::class . ')',
            $this->getOwner()
        );
    }
}

/**
 * @extends Extension<Foo&Bar>
 */
class InstersectionExtension extends Extension
{
    public function baz(): void
    {
        assertType(
            '(' . Bar::class . '&' . Foo::class . ')|static(' . self::class . ')',
            $this->getOwner()
        );
    }
}
