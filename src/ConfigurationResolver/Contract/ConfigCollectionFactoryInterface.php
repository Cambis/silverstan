<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

use SilverStripe\Config\Collections\ConfigCollectionInterface;

interface ConfigCollectionFactoryInterface
{
    public function create(): ConfigCollectionInterface;
}
