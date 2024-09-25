<?php

namespace Cambis\Silverstan\Tests\Rule\PropertyFetch\Source\Model;

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

    public function nestedWrite(): void
    {
        $this->write();
    }
}
