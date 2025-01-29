<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\ORM\FieldType\DBField;

use SilverStripe\ORM\FieldType\DBField;

/**
 * @property list<string> $value
 */
final class FooField extends DBField
{
    public function requireField(): void
    {
        // Noop
    }
}
