<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;

interface ConfigurationResolverAwareInterface
{
    /**
     * @return static
     */
    public function setConfigurationResolver(ConfigurationResolver $configurationResolver);
}
