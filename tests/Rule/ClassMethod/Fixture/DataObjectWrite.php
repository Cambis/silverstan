<?php

namespace Cambis\Silverstan\Tests\Rule\ClassMethod\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class DataObjectWrite extends DataObject implements TestOnly
{
    public function requireDefaultRecords(): void
    {
        if (true) {
        }

        parent::requireDefaultRecords();
    }

    protected function onAfterWrite(): void
    {
        parent::onAfterWrite();
    }

    protected function onBeforeWrite(): void
    {
    }
}
