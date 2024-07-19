<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\NotATrait;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleNotATrait implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend NotATrait
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
