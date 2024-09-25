<?php

namespace {
    use SilverStripe\CMS\Model\SiteTree;

    if (!class_exists(SilverStripe\CMS\Model\SiteTree::class)) {
        return;
    }

    class Page extends SiteTree
    {
    }
}
