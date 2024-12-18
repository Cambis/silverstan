<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigCollectionFactoryInterface;
use Override;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Collections\MemoryConfigCollection;
use SilverStripe\Core\Config\ConfigLoader;

final class LazyConfigCollectionFactory implements ConfigCollectionFactoryInterface
{
    #[Override]
    public function create(): ConfigCollectionInterface
    {
        // Return an empty collection if there is no manifest
        if (!ConfigLoader::inst()->hasManifest()) {
            return new MemoryConfigCollection();
        }

        return ConfigLoader::inst()->getManifest();
    }
}
