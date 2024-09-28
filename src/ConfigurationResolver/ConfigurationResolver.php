<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use SilverStripe\Core\Config\Config;

final class ConfigurationResolver
{
    /**
     * @param class-string $className
     */
    public function get(string $className, string $name): mixed
    {
        return Config::inst()->get($className, $name, Config::EXCLUDE_EXTRA_SOURCES | Config::UNINHERITED);
    }
}
