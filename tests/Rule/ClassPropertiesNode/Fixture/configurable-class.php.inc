<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\Fixture;

final class ConfigurableClass implements \SilverStripe\Dev\TestOnly
{
    use \SilverStripe\Core\Config\Configurable;

    private static string $configurable_property = 'foo';

    private string $unconfigurable_property = 'bar';

    /**
     * @internal
     */
    private static string $blocklisted_property = 'baz';
}
