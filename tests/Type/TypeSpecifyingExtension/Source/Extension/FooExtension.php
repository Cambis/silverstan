<?php

namespace Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Source\Extension;

use Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<(Foo & static)>
 */
final class FooExtension extends Extension implements TestOnly
{
    protected function doSomething(): bool
    {
        return true;
    }
}
