<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use Cambis\Silverstan\Tests\Rule\MethodCall\Fixture\Contract\MethodSignatureDoesNotMatchNumberOfParams;
use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleMethodSignatureDoesNotMatchNumberOfParams implements TestOnly
{
    use Extensible;

    /**
     * @phpstan-silverstripe-extend MethodSignatureDoesNotMatchNumberOfParams
     */
    public function getFoo(): string
    {
        $foo = 'Title';

        $this->extend('updateFoo', $foo);

        return $foo;
    }
}
