<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Fixture;

use SilverStripe\Forms\FormField;
use UncleCheese\DisplayLogic\Criteria;
use function PHPStan\Testing\assertType;

$products = FormField::create();
$sizes = FormField::create();
$payment = FormField::create();
$shipping = FormField::create();

assertType(
    Criteria::class,
    $products->displayIf('HasProducts')->isChecked()
);

assertType(
    Criteria::class,
    $sizes->hideUnless('ProductType')->isEqualTo('t-shirt')
        ->andIf('Price')->isGreaterThan(10)
);

assertType(
    Criteria::class,
    $payment->hideIf('Price')->isEqualTo(0)
);

assertType(
    FormField::class,
    $shipping->displayIf('ProductType')
        ->isEqualTo('furniture')
        ->andIf()
        ->group()
        ->orIf('RushShipping')->isChecked()
        ->orIf('ShippingAddress')->isNotEmpty()
        ->end()
);
