<?php

namespace Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\Fixture;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

final class ConfigurableExtension extends Extension implements TestOnly
{
    private static string $configurable_property = 'foo';

    private string $unconfigurable_property = 'bar';

    /**
     * @internal
     */
    private static string $blocklisted_property = 'baz';
}
