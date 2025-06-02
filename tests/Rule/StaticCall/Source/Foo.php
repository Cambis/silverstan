<?php

namespace Cambis\Silverstan\Tests\Rule\StaticCall\Source;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Dev\TestOnly;

final readonly class Foo implements TestOnly
{
    use Injectable;

    public function __construct(
        public string $param1,
        public int $param2
    ) {
    }
}
