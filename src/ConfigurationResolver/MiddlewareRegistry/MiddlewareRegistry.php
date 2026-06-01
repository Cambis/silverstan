<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\MiddlewareRegistry;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigurationResolverAwareInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryInterface;
use Override;
use SilverStripe\Config\Middleware\Middleware as MiddlewareInterface;

final class MiddlewareRegistry implements MiddlewareRegistryInterface
{
    /**
     * @readonly
     */
    private array $middlewares;
    public function __construct(
        ConfigurationResolver $configurationResolver,
        array $middlewares
    ) {
        /**
         * @var MiddlewareInterface[]
         */
        $this->middlewares = $middlewares;
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof ConfigurationResolverAwareInterface) {
                continue;
            }

            $middleware->setConfigurationResolver($configurationResolver);
        }

    }

    #[Override]
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
