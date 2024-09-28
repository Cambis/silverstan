<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension;

use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<Foo>
 */
final class SimpleExtension extends Extension implements TestOnly
{
}
