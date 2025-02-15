<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolverRegistryProvider;

use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryProviderInterface;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolverRegistry\ReflectionResolverRegistry;
use PHPStan\DependencyInjection\Container;
use function array_reverse;

final class LazyReflectionResolverRegistryProvider implements ReflectionResolverRegistryProviderInterface
{
    /**
     * @readonly
     */
    private Container $container;
    private ?ReflectionResolverRegistryInterface $registry = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getRegistry(): ReflectionResolverRegistryInterface
    {
        if (!$this->registry instanceof ReflectionResolverRegistryInterface) {
            $this->registry = new ReflectionResolverRegistry(
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(PropertyReflectionResolverInterface::SERVICE_NAME)),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(MethodReflectionResolverInterface::SERVICE_NAME))
            );
        }
        return $this->registry;
    }
}
