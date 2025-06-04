<?php

namespace Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\TestOnly;

final class DisallowPropertyFetch implements TestOnly
{
    use Configurable;

    private static string $foo = 'foo';

    public function bar(): void
    {
        $this->config()->foo;
        $this->config()->get('foo');

        static::config()->foo;
        static::config()->get('foo');

        self::config()->foo;
        self::config()->get('foo');

        self::config()->foo = 'bar';
    }
}
