<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

interface MiddlewareRegistryProviderInterface
{
    public function getRegistry(): MiddlewareRegistryInterface;
}
