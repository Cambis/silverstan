<?php

namespace Cambis\Silverstan\Tests\Rules\Properties\Fixture;

final class ConfigurableClass
{
    use \SilverStripe\Core\Config\Configurable;

    private static string $configurable_property = 'foo';

    private string $unconfigurable_property = 'bar';

    /**
     * @internal
     */
    private static string $blocklisted_property = 'baz';
}
