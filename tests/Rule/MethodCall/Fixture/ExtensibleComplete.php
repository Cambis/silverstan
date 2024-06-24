<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract\UpdateFoo;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleComplete implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend UpdateFoo
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
