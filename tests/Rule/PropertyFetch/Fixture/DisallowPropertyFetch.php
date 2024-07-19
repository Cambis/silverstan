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
        echo static::config()->foo;
        echo static::config()->get('foo');
    }
}
