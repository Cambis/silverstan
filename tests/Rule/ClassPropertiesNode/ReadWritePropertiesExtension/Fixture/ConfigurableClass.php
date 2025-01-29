<?php

namespace Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ReadWritePropertiesExtension\Fixture;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\TestOnly;

final class ConfigurableClass implements TestOnly
{
    use Configurable;

    private static string $configurable_property = 'foo';

    private string $unconfigurable_property = 'bar';

    /**
     * @internal
     */
    private static string $blocklisted_property = 'baz';
}
