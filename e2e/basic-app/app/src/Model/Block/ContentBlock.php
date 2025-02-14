<?php

namespace App\Model\Block;

use App\Model\Block;

final class ContentBlock extends Block
{
    private static string $table_name = 'ContentBlock';

    private static array $db = [
        'Content' => 'HTMLText',
    ];
}
