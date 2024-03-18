<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class RequireSnakeCase extends DataObject implements TestOnly
{
    private static string $fooBar = 'foo bar';

    /**
     * @internal
     */
    private static string $fooBarBaz = 'foo bar baz';
}
