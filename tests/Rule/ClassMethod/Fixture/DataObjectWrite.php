<?php

namespace Cambis\Silverstan\Rule\ClassMethod\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class DataObjectWrite extends DataObject implements TestOnly
{
    protected function onAfterWrite(): void
    {
        parent::onAfterWrite();
    }

    protected function onBeforeWrite(): void
    {
    }

    public function requireDefaultRecords(): void
    {
        if (true) {
        }

        parent::requireDefaultRecords();
    }
}
