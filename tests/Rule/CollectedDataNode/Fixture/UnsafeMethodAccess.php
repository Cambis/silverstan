<?php

namespace Cambis\Silverstan\Tests\Rule\CollectedDataNode\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * @method DataObject Relation()
 */
final class UnsafeMethodAccess extends DataObject implements TestOnly
{
    public function doSomethingDangerous(): string
    {
        return $this->Relation()->getTitle();
    }

    public function doSomethingSafe(): string
    {
        if (!$this->Relation()->exists()) {
            return '';
        }

        return $this->Relation()->getTitle();
    }
}
