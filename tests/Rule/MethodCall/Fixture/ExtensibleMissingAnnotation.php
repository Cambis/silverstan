<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleMissingAnnotation implements TestOnly
{
    use Extensible;

    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
