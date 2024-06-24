<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleUnresolveableAnnotation implements TestOnly
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
