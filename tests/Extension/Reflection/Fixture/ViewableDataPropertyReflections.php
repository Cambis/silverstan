<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\View\ViewableData;
use function PHPStan\Testing\assertType;

$foo = new class extends ViewableData implements TestOnly {};

assertType('null', $foo->Bar);
