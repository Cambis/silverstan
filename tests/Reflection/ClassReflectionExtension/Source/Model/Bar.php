<?php

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\Source\Model;

use SilverStripe\Dev\TestOnly;

final class Bar extends Foo implements TestOnly
{
    private static array $has_many = [
        'CaregiversOne' => Foo::class . '.CarerOf',
        'CaregiversTwo' => Foo::class . '.CarerOf',
    ];
}
