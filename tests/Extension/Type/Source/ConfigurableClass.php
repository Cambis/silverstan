<?php

namespace Cambis\Silverstan\Tests\Extension\Type\Source;

use SilverStripe\Core\Config\Configurable;

class ConfigurableClass
{
    use Configurable;

    private static array $native_array = [];

    /**
     * @var array
     */
    private static $phpdoc_array = [];

    /**
     * @var string[]
     */
    private static array $iterable_typed_array = [];

    private static bool $native_boolean = false;

    /**
     * @var bool
     */
    private static $phpdoc_boolean = false;

    private static int $native_integer = 0;

    /**
     * @var int
     */
    private static $phpdoc_integer = 0;

    private static string $native_string = '';

    /**
     * @var string
     */
    private static $phpdoc_string = '';

    private static $mixed = '';
}
