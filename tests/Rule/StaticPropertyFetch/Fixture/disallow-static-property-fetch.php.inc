<?php

namespace Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\Fixture;

final class DisallowStaticPropertyFetch implements \SilverStripe\Dev\TestOnly
{
    use \SilverStripe\Core\Config\Configurable;

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
