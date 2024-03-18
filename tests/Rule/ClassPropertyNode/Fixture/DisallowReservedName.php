<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class DisallowReservedName extends DataObject implements TestOnly
{
    public static array $db = [];

    private static string $table_name = '';
}
