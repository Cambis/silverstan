<?php

namespace Cambis\Silverstan\Tests\PhpDoc\TypeNodeResolverExtension\Source\Extension;

use Cambis\Silverstan\Tests\PhpDoc\TypeNodeResolverExtension\Source\Model\Bar;
use Cambis\Silverstan\Tests\PhpDoc\TypeNodeResolverExtension\Source\Model\Foo;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<Foo&Bar&static>
 */
final class ComplexIntersectionExtension extends Extension implements TestOnly
{
}
