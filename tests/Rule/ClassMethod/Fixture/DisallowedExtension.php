<?php

namespace Cambis\Silverstan\Tests\Rule\ClassMethod\Fixture;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<static>
 */
final class DisallowedExtension extends Extension implements TestOnly
{
    public function publicMethod(): void
    {
    }

    private function privateMethod(): void
    {
    }
}
