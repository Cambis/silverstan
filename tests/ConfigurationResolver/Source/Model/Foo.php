<?php

namespace Cambis\Silverstan\Tests\ConfigurationResolver\Source\Model;

use SilverStripe\Core\Config\Configurable;

class Foo
{
    use Configurable;

    /**
     * @var string[]
     */
    private static array $first = ['test_1'];

    /**
     * @var string[]
     */
    private static array $second = ['test_1'];

    /**
     * @var string[]
     */
    private static array $third = ['test_1'];
}
