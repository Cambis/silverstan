<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

use SilverStripe\Dev\TestOnly;

final class Bar extends Foo implements TestOnly
{
    private static array $db = [];

    private static string|null $table_name = 'MyTable';

    private static string $deprecated_property = '';

    private static string $deprecated_property_with_message = '';
}
