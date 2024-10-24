<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Source\Model;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Extension\FooExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * @property string $TypehintedField
 * @method Foo TypehintedHasOne()
 * @method HasManyList<Foo> TypehintedHasMany()
 */

class Foo extends DataObject implements TestOnly
{
    /**
     * @var self[]
     */
    public array $typedDiArray;

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
        'RequiredField' => 'Varchar(255)',
        'TypehintedField' => 'Varchar(255)',
    ];

    private static array $belongs_many_many = [
        'Ancestors' => self::class,
    ];

    private static array $belongs_to = [
        'Parent' => self::class . '.Parent',
    ];

    private static array $has_one = [
        'Child' => self::class . '.Child',
    ];

    private static array $has_many = [
        'Siblings' => self::class,
    ];

    private static array $many_many = [
        'Family' => self::class,
        'ExtendedFamily' => [
            'through' => self::class,
            'from' => 'Me',
            'to' => 'You',
        ],
    ];

    private static array $extensions = [
        FooExtension::class,
    ];

    private static array $dependencies = [
        'diObject' => '%$' . self::class,
        'diArray' => [
            '%$' . self::class,
        ],
        'typedDiArray' => [
            '%$' . self::class,
        ],
    ];
}
