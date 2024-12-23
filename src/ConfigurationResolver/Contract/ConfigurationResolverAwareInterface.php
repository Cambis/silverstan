<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;

interface ConfigurationResolverAwareInterface
{
    public function setConfigurationResolver(ConfigurationResolver $configurationResolver): static;
}
