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
    private static array $db = [
        'ExtensionField' => 'Varchar(255)',
    ];

    public function publicMethod(): bool
    {
        return true;
    }

    protected function protectedMethod(): bool
    {
        return true;
    }

    private function privateMethod(): bool
    {
        return true;
    }
}
