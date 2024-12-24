<?php

namespace Cambis\Silverstan\Tests\ConfigurationResolver\Source\Model;

use Cambis\Silverstan\Tests\ConfigurationResolver\Source\Extension\BarExtension;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

final class Bar extends Foo implements TestOnly
{
    /**
     * @var string[]
     */
    private static array $first = ['test_2'];

    /**
     * @var string[]
     */
    private static array $second = ['test_2'];

    /**
     * @var string[]
     */
    private static array $third = ['test_2'];

    /**
     * @var class-string<Extension>
     */
    private static array $extensions = [
        BarExtension::class,
    ];
}
