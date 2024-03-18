<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class DisallowOverridingName extends DataObject implements TestOnly
{
    private static array $db = [];

    private static string|null $table_name = 'MyTable';
}
