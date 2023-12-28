<?php

namespace Cambis\Silverstan\Tests\Rules\Properties\Fixture;

final class ConfigurableExtension extends \SilverStripe\Core\Extension
{
    private static string $configurable_property = 'foo';

    private string $unconfigurable_property = 'bar';

    /**
     * @internal
     */
    private static string $blocklisted_property = 'baz';
}
