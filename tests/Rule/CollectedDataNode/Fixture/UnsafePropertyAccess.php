<?php

namespace Cambis\Silverstan\Tests\Rule\CollectedDataNode\Fixture;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * @extends Extension<static>
 * @method DataObject Relation()
 */
final class UnsafePropertyAccess extends Extension implements TestOnly
{
    public function doSomethingDangerous(): string
    {
        return $this->Relation()->Title;
    }

    public function doSomethingSafe(): string
    {
        if (!$this->Relation()->exists()) {
            return '';
        }

        return $this->Relation()->Title;
    }
}
