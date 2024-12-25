<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigurationResolverAwareInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryInterface;
use Override;
use SilverStripe\Config\Middleware\Middleware as MiddlewareInterface;

final readonly class MiddlewareRegistry implements MiddlewareRegistryInterface
{
    public function __construct(
        ConfigurationResolver $configurationResolver,
        /**
         * @var MiddlewareInterface[]
         */
        private array $middlewares,
    ) {
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
