<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\ConfigCollection;

use SilverStripe\Config\Collections\MemoryConfigCollection;

final class SimpleConfigCollection extends MemoryConfigCollection
{
    /**
     * @param array<string, mixed[]> $config
     */
    public function __construct(
        array $config
    ) {
        parent::__construct(false);

        $this->config = $config;
    }
}
