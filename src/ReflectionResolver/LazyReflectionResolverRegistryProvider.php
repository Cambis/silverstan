<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver;

use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryProviderInterface;
use Override;
use PHPStan\DependencyInjection\Container;
use function array_reverse;

final class LazyReflectionResolverRegistryProvider implements ReflectionResolverRegistryProviderInterface
{
    private ?ReflectionResolverRegistryInterface $registry = null;

    public function __construct(
        private readonly Container $container
    ) {
    }

    #[Override]
    public function getRegistry(): ReflectionResolverRegistryInterface
    {
        if (!$this->registry instanceof ReflectionResolverRegistryInterface) {
            $this->registry = new ReflectionResolverRegistry(
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag('silverstan.reflectionResolver.propertyReflectionResolver')),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag('silverstan.reflectionResolver.methodReflectionResolver'))
            );
        }

        return $this->registry;
    }
}
