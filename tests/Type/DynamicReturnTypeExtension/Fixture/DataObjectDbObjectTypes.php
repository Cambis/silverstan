<?php

namespace Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\Fixture;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Foo;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\FieldType\DBEnum;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBPercentage;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBTime;
use SilverStripe\ORM\FieldType\DBVarchar;
use function PHPStan\Testing\assertType;

$foo = Foo::create();

assertType(DBBoolean::class, $foo->dbObject('Boolean'));
assertType(DBCurrency::class, $foo->dbObject('Currency'));
assertType(DBDate::class, $foo->dbObject('Date'));
assertType(DBDecimal::class, $foo->dbObject('Decimal'));
assertType(DBEnum::class, $foo->dbObject('Enum'));
assertType(DBHTMLText::class, $foo->dbObject('HTMLText'));
assertType(DBHTMLVarchar::class, $foo->dbObject('HTMLVarchar'));
assertType(DBInt::class, $foo->dbObject('Int'));
assertType(DBPercentage::class, $foo->dbObject('Percentage'));
assertType(DBText::class, $foo->dbObject('Text'));
assertType(DBTime::class, $foo->dbObject('Time'));
assertType(DBVarchar::class, $foo->dbObject('Varchar'));
