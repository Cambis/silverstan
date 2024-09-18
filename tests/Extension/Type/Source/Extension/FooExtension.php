<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Source\Extension;

use Cambis\Silverstan\Tests\Extension\Type\Source\Model\Foo;
use SilverStripe\Core\Extension;

/**
 * @extends Extension<(Foo & static)>
 */
final class FooExtension extends Extension
{
    protected function doSomething(): bool
    {
        return true;
    }
}
