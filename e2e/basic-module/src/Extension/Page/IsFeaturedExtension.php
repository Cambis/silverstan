<?php

namespace App\Extension\Page;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;

final class IsFeaturedExtension extends Extension
{
    private static array $db = [
        'IsFeatured' => 'Boolean(0)',
    ];

    protected function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldToTab('Root.Main', CheckboxField::create('IsFeatured'));
    }
}
