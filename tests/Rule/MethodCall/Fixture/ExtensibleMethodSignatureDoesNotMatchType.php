<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract\MethodSignatureDoesNotMatchType;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleMethodSignatureDoesNotMatchType implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend MethodSignatureDoesNotMatchType
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
