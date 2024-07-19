<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class Foo extends DataObject implements TestOnly
{
    public function foo(): string
    {
        return 'foo';
    }
}
