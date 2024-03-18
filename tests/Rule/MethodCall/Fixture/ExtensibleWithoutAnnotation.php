<?php

namespace Cambis\Silverstan\Tests\Rule\MethodCall\Fixture;

use SilverStripe\Core\Extensible;
use SilverStripe\Dev\TestOnly;

final class ExtensibleWithoutAnnotation implements TestOnly
{
    use Extensible;

    public function getTitle(): string
    {
        $title = 'Title';

        $this->extend('updateTitle', $title);

        return $title;
    }
}
