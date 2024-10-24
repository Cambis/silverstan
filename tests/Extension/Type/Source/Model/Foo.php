<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Source\Model;

use SilverStripe\ORM\DataObject;

/**
 * @method Foo Bar()
 */
final class Foo extends DataObject
{
    private static array $db = [
        'Boolean' => 'Boolean',
        'Currency' => 'Currency',
        'Date' => 'Date',
        'Decimal' => 'Decimal',
        'Enum' => 'Enum',
        'HTMLText' => 'HTMLText',
        'HTMLVarchar' => 'HTMLVarchar',
        'Int' => 'Int',
        'Percentage' => 'Percentage',
        'Datetime' => 'Datetime',
        'Text' => 'Text',
        'Time' => 'Time',
        'Varchar' => 'Varchar(255)',
    ];
}
