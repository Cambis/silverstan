<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Source\Extension;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<(Foo & static)>
 */
final class FooExtension extends Extension implements TestOnly
{
}
