<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Source\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * @method Foo Bar()
 */
final class Foo extends DataObject implements TestOnly
{
    /**
     * @return static
     */
    public function Baz()
    {
        return $this;
    }

    public function doSomethingPotentiallyDangerous(): bool
    {
        return true;
    }

    public function doSomethingSafe(): bool
    {
        return true;
    }
}
