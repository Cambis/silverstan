<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

final class DisallowOverridingName extends \SilverStripe\ORM\DataObject implements \SilverStripe\Dev\TestOnly
{
    private static array $db = [];

    private static string|null $table_name = 'MyTable';
}
