<?php

declare(strict_types=1);

// Add Page/PageController stubs which may be required
if (!class_exists(Page::class)) {
    require __DIR__ . '/stubs/Page.php';
}

if (!class_exists(PageController::class)) {
    require __DIR__ . '/stubs/PageController.php';
}
