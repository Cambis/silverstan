<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Extension;

use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model\Bar;
use Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<Foo|Bar>
 */
final class UnionExtension extends Extension implements TestOnly
{
}
