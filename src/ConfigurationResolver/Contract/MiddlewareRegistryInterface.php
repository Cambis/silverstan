<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

use SilverStripe\Config\Middleware\Middleware as MiddlewareInterface;

interface MiddlewareRegistryInterface
{
    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array;
}
