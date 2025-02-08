<?php

namespace App\Extension;

use App\Model\Foo;
use SilverStripe\Core\Extension;

/**
 * @extends Extension<Foo & static>
 */
final class FooExtension extends Extension
{
    public function doSomething(): string
    {
        return '';
    }
}
