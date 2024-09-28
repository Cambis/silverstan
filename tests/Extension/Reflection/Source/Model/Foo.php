<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Source\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\DataObject;

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
    ];

    public function getCMSCompositeValidator(): CompositeValidator
    {
        return parent::getCMSCompositeValidator()
            ->addValidator(RequiredFields::create(['RequiredField']));
    }
}
