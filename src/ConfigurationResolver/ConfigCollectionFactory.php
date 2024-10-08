<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Core\Config\Config;

final class ConfigCollectionFactory
{
    /**
     * @api
     */
    public function create(): ConfigCollectionInterface
    {
        return Config::inst();
    }
}
