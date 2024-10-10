<?php

namespace {
    use SilverStripe\CMS\Model\SiteTree;

    if (!class_exists(SiteTree::class)) {
        return;
    }

    class Page extends SiteTree
    {
    }
}
