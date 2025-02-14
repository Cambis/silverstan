<?php

use App\Model\Block;
use App\Page\Extension\AlternativeTitleExtension;
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
