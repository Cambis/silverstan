<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Extension\FooExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectSchema;
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
        'BooleanDefaultFalseWithSpace' => 'Boolean (false)',
        'MalformedField' => 'Barchar(255)',
        'FieldWithGetter' => 'Varchar(255)',
        'FieldWithDefaultValueGetter' => 'Varchar(255)',
        'FooField' => 'FooField',
    ];

    private static array $belongs_many_many = [
        'Ancestors' => self::class,
    ];

    private static array $belongs_to = [
        'Parent' => self::class . '.Parent',
    ];

    private static array $has_one = [
        'Child' => self::class . '.Child',
        'CarerOf' => [
            'class' => DataObject::class,
            DataObjectSchema::HAS_ONE_MULTI_RELATIONAL => true,
        ],
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
        'fooExtension' => FooExtension::class,
        'nullifiedExtension' => null,
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

    public function getFieldWithGetter(): string
    {
        return $this->getField('FieldWithGetter') ?? '';
    }

    /**
     * @return non-empty-string
     */
    public function getFieldWithDefaultValueGetter(): string
    {
        return $this->getField('FieldWithDefaultValueGetter') ?? 'Default value';
    }
}
