<?php

namespace Cambis\Silverstan\Tests\Extension\Reflection\Fixture;

use Cambis\Silverstan\Tests\Extension\Reflection\Source\Model\Bar;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Model\ModelData;
use function PHPStan\Testing\assertType;

$foo = new class extends ModelData implements TestOnly {};

assertType('mixed', $foo->Bar);

if ($foo->Bar instanceof Bar) {
    assertType(Bar::class, $foo->Bar);
}

$foo->Baz = 'baz';

assertType("'baz'", $foo->Baz);
