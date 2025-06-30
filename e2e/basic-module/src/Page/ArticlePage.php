<?php

namespace App\Page;

use Page;

class ArticlePage extends Page
{
    private static string $table_name = 'ArticlePage';

    public function isFeatured(): bool
    {
        return $this->IsFeatured;
    }
}
