<?php

namespace {
    use SilverStripe\CMS\Controllers\ContentController;

    if (!class_exists(ContentController::class)) {
        return;
    }

    /**
     * @template T of \Page
     * @extends ContentController<T>
     */
    class PageController extends ContentController
    {
    }
}
