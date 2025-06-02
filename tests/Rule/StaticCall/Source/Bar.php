<?php

namespace Cambis\Silverstan\Tests\Rule\StaticCall\Source;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Dev\TestOnly;

final class Bar implements TestOnly
{
    use Injectable;
}
