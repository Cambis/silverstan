<?php

namespace App\Model;

use SilverStripe\ORM\DataObject;

final class BasicModel extends DataObject
{
    private static string $table_name = 'BasicModel';

    public function testDelete(): void
    {
        // Should not throw:
        //
        // Call to method SilverStripe\ORM\DataObject::delete() will always evaluate to true.
        // 🪪  method.alreadyNarrowedType
        self::create()->delete();
    }
}
