<?php

namespace Cambis\Silverstan\Tests\Rule\ClassMethod\Fixture;

use SilverStripe\Core\Extension;

/**
 * @extends Extension<static>
 */
class DisallowedExtension extends Extension
{
    public function publicMethod(): void
    {
    }

    private function privateMethod(): void
    {
    }
}
