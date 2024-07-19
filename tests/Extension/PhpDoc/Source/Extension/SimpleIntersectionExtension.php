<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension;

use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<Foo&static>
 */
final class SimpleIntersectionExtension extends Extension implements TestOnly
{
}
