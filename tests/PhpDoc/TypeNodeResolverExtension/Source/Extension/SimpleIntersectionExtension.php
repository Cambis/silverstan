<?php

namespace Cambis\Silverstan\Tests\PhpDoc\TypeNodeResolverExtension\Source\Extension;

use Cambis\Silverstan\Tests\PhpDoc\TypeNodeResolverExtension\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<Foo&static>
 */
final class SimpleIntersectionExtension extends Extension implements TestOnly
{
}
