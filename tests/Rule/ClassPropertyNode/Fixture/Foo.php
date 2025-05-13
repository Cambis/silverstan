<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class Foo extends DataObject implements TestOnly
{
    /**
     * @deprecated
     */
    private static string $deprecated_property = '';

    /**
     * @deprecated reason
     */
    private static string $deprecated_property_with_message = '';
}
