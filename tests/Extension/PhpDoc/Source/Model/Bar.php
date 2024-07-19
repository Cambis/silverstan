<?php

namespace Cambis\Silverstan\Tests\Extension\PhpDoc\Source\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class Bar extends DataObject implements TestOnly
{
    public function bar(): bool
    {
        return true;
    }
}
