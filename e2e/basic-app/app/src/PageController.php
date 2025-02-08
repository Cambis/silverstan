<?php

use SilverStripe\CMS\Controllers\ContentController;

class PageController extends ContentController
{
    private static array $has_many = [
        'Blocks' => 'HTMLText',
    ];
}
