<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture;

final class DisallowReservedName extends \SilverStripe\ORM\DataObject implements \SilverStripe\Dev\TestOnly
{
    public static array $db = [];

    private static string $table_name = '';
}
