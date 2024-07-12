<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodNonVoidReturnType;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleMethodNonVoidReturnType implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend MethodNonVoidReturnType
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
