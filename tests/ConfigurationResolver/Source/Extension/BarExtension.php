<?php

namespace Cambis\Silverstan\Tests\ConfigurationResolver\Source\Extension;

use Cambis\Silverstan\Tests\ConfigurationResolver\Source\Model\Bar;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * @extends Extension<(Bar & static)>
 */
final class BarExtension extends Extension implements TestOnly
{
    /**
     * @var string[]
     */
    private static array $second = ['test_3'];

    /**
     * @var string[]
     */
    private static array $third = ['test_3'];

    public static function get_extra_config(string $className, string $extensionName, array $args): array
    {
        return [
            'fourth' => ['test_4'],
        ];
    }
}
