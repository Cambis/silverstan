<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

interface MiddlewareRegistryInterface
{
    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array;
}
