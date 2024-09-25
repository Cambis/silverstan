<?php

namespace {
    use SilverStripe\CMS\Controllers\ContentController;

    if (!class_exists(SilverStripe\CMS\Controllers\ContentController::class)) {
        return;
    }

    class PageController extends ContentController
    {
    }
}
