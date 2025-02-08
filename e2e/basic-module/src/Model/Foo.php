<?php

namespace App\Model;

use App\Extension\FooExtension;
use SilverStripe\ORM\DataObject;

final class Foo extends DataObject
{
    private static string $table_name = 'Foo';

    /**
     * @var string[]
     */
    private static array $extensions = [
        FooExtension::class,
    ];

    public function doSomethingElse(): string
    {
        return $this->doSomething();
    }
}
