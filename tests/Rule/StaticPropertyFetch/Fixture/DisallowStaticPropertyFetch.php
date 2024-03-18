<?php

namespace Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\Fixture;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\TestOnly;

final class DisallowStaticPropertyFetch implements TestOnly
{
    use Configurable;

    private static string $foo = 'foo';

    private string $bar = 'bar';

    /**
     * @internal
     */
    private static string $baz = 'baz';

    public function bar(): void
    {
        echo self::$foo;
        echo self::$bar;
        echo self::$baz;
    }
}
