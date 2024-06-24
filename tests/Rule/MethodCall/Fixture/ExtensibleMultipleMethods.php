<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract\MultipleMethods;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleMultipleMethods implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend MultipleMethods
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
