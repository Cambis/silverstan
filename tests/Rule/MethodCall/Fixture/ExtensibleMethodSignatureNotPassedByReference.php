<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Concern\MethodSignatureNotPassedByReference;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleMethodSignatureNotPassedByReference implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend MethodSignatureNotPassedByReference
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
