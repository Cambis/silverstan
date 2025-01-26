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

    private static array $has_one = [
        'ExtensionHasOne' => Foo::class . '.extension',
    ];

    public function publicMethod(bool $arg): bool
    {
        return $arg;
    }

    public static function publicStaticMethod(bool $arg): bool
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
