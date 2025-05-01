<?php

namespace Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension\Fixture;

use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;
use function PHPStan\Testing\assertType;

// Controller is a subclass of ViewableData/ModelData
$foo = new class extends Controller implements TestOnly {};

if (!$foo->hasField('Bar')) {
    assertType('null', $foo->Bar);
}

if ($foo->hasField('Bar')) {
    assertType('mixed', $foo->Bar);
}
