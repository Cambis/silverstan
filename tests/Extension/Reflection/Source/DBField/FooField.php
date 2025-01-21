<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Source\DBField;

use SilverStripe\ORM\FieldType\DBField;

final class FooField extends DBField
{
    public function requireField(): void
    {
        // Noop
    }
}
