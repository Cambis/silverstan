<?php

namespace Cambis\Silverstan\Tests\Reflection\Deprecation\MethodDeprecationExtension\Source\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class Foo extends DataObject implements TestOnly
{
    public function getCMSValidator(): void
    {
    }
}
