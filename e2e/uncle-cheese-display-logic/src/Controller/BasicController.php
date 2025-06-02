<?php

declare(strict_types=1);

namespace App\Controller;

use SilverStripe\Forms\FormField;
use UncleCheese\DisplayLogic\Criteria;

final class BasicController
{
    public function getProducts(): Criteria
    {
        $products = FormField::create('Products');

        return $products->displayIf('HasProducts')->isChecked();
    }

    public function getSizes(): Criteria
    {
        $sizes = FormField::create('Sizes');

        return $sizes->hideUnless('ProductType')->isEqualTo('t-shirt')
            ->andIf('Price')->isGreaterThan(10);
    }

    public function getPayment(): Criteria
    {
        $payment = FormField::create('Payment');

        return $payment->hideIf('Price')->isEqualTo(0);
    }

    public function getShipping(): FormField
    {
        $shipping = FormField::create('Shipping');

        return $shipping->displayIf('ProductType')
            ->isEqualTo('furniture')
            ->andIf()
            ->group()
            ->orIf('RushShipping')->isChecked()
            ->orIf('ShippingAddress')->isNotEmpty()
            ->end();
    }
}
