<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Contract;

use SilverStripe\Config\Middleware\Middleware;

interface MiddlewareInterface extends Middleware
{
    /**
     * @var string
     */
    final public const SERVICE_NAME = 'silverstan.configurationResolver.middleware';
}
