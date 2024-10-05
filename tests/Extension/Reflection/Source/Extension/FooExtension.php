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

    public function publicMethod(bool $arg): bool
    {
        return $arg;
    }

    protected function protectedMethod(bool $arg): bool
    {
        return $arg;
    }

    private function privateMethod(bool $arg): bool
    {
        return $arg;
    }
}
