<?php

namespace Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\Fixture;

final class ConfigurableExtension extends \SilverStripe\Core\Extension implements \SilverStripe\Dev\TestOnly
{
    private static string $configurable_property = 'foo';

    private string $unconfigurable_property = 'bar';

    /**
     * @internal
     */
    private static string $blocklisted_property = 'baz';
}
