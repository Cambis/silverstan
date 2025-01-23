<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Source\ORM\FieldType\DBField;

use SilverStripe\ORM\FieldType\DBField;

final class FooField extends DBField
{
    public function requireField(): void
    {
        // Noop
    }

    /**
     * @return list<string>
     */
    public function getValue()
    {
        return [''];
    }
}
