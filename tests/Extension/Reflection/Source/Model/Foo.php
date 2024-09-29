<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Source\Model;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Extension\FooExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;

/**
 * @property string $TypehintedField
 */

final class Foo extends DataObject implements TestOnly
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

    public function getCMSCompositeValidator(): CompositeValidator
    {
        return parent::getCMSCompositeValidator()
            ->addValidator(RequiredFields::create(['RequiredField']));
    }
}
