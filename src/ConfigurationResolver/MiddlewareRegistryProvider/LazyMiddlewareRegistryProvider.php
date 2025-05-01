<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\MiddlewareRegistryProvider;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryInterface;
use Cambis\Silverstan\ConfigurationResolver\Contract\MiddlewareRegistryProviderInterface;
use Cambis\Silverstan\ConfigurationResolver\MiddlewareRegistry\MiddlewareRegistry;
use PHPStan\DependencyInjection\Container;
use function array_reverse;

final class LazyMiddlewareRegistryProvider implements MiddlewareRegistryProviderInterface
{
    /**
     * @readonly
     */
    private Container $container;
    private ?MiddlewareRegistryInterface $registry = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getRegistry(): MiddlewareRegistryInterface
    {
        if (!$this->registry instanceof MiddlewareRegistryInterface) {
            $this->registry = new MiddlewareRegistry(
                $this->container->getByType(ConfigurationResolver::class),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag('silverstan.configurationResolver.middleware')),
            );
        }
        return $this->registry;
    }
}
