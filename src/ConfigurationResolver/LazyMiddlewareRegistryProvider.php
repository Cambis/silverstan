<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryProviderInterface;
use Override;
use PHPStan\DependencyInjection\Container;
use function array_reverse;

final class LazyMiddlewareRegistryProvider implements MiddlewareRegistryProviderInterface
{
    private ?MiddlewareRegistryInterface $registry = null;

    public function __construct(
        private readonly Container $container
    ) {
    }

    #[Override]
    public function getRegistry(): MiddlewareRegistryInterface
    {
        if (!$this->registry instanceof MiddlewareRegistryInterface) {
            $this->registry = new MiddlewareRegistry(
                $this->container->getByType(ConfigurationResolver::class),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(MiddlewareInterface::SERVICE_NAME)),
            );
        }

        return $this->registry;
    }
}
