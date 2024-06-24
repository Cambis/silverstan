<?php

namespace Cambis\Silverstan\Tests\Rule\Variable\Fixture;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\TestOnly;

final class DisallowSuperglobals implements TestOnly
{
    public function foo(HTTPRequest $request): void
    {
        $bar = $_GET['bar'];
    }
}
