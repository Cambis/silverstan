<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Fixture;

use Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model\Bar;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;
use function PHPStan\Testing\assertType;

$foo = new class extends Controller implements TestOnly {};

assertType('mixed', $foo->Bar);

if ($foo->Bar instanceof Bar) {
    assertType(Bar::class, $foo->Bar);
}

$foo->Baz = 'baz';

assertType("'baz'", $foo->Baz);
