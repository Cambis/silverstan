<?php

namespace App\Page;

use App\PageController\HomepageController;
use Page;

final class Homepage extends Page
{
    private static string $table_name = 'Homepage';

    private static string $controller_name = HomepageController::class;
}
