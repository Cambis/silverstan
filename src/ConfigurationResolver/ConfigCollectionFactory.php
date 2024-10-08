<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Collections\MemoryConfigCollection;
use SilverStripe\Core\Config\ConfigLoader;

final class ConfigCollectionFactory
{
    /**
     * @api
     */
    public function create(): ConfigCollectionInterface
    {
        // Return an empty collection before bootstrapping
        if (!ConfigLoader::inst()->hasManifest()) {
            return new MemoryConfigCollection();
        }

        return ConfigLoader::inst()->getManifest();
    }
}
