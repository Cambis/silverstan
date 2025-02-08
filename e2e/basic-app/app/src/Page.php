<?php

use App\Block;
use App\Extension\Page\AlternativeTitleExtension;
use SilverStripe\CMS\Model\SiteTree;

class Page extends SiteTree
{
    private static array $has_many = [
        'Blocks' => Block::class,
    ];

    private static array $extensions = [
        AlternativeTitleExtension::class,
    ];
}
