<?php

namespace Cambis\Silverstan\Tests\Rule\InClassMethodNode\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class DisallowGetCMSValidator extends DataObject implements TestOnly
{
    public function getCMSValidator()
    {
        return null;
    }
}
