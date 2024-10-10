<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Fixture;

use SilverStripe\Dev\TestOnly;
use SilverStripe\View\ViewableData;
use function PHPStan\Testing\assertType;

$foo = new class extends ViewableData implements TestOnly {};

if (!$foo->hasField('Bar')) {
    assertType('null', $foo->Bar);
}

if ($foo->hasField('Bar')) {
    assertType('mixed', $foo->Bar);
}
