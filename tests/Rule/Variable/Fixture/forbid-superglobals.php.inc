<?php 

namespace Cambis\Silverstan\Tests\Rule\Variable\Fixture;

final class ForbidSuperGlobalsRule implements \SilverStripe\Dev\TestOnly
{
    public function foo(\SilverStripe\Control\HTTPRequest $request): void
    {
        $bar = $_GET['bar'];
    }
}
