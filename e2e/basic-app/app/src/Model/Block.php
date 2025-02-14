<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

class Block extends DataObject
{
    private static string $table_name = 'Block';

    private static array $db = [
        'Title' => 'Varchar(255)',
    ];
}
