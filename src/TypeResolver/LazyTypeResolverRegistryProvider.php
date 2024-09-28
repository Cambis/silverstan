<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryProviderInterface;
use Override;
use PHPStan\DependencyInjection\Container;
use function array_reverse;

final class LazyTypeResolverRegistryProvider implements TypeResolverRegistryProviderInterface
{
    private ?TypeResolverRegistryInterface $registry = null;

    public function __construct(
        private readonly Container $container
    ) {
    }

    #[Override]
    public function getRegistry(): TypeResolverRegistryInterface
    {
        if (!$this->registry instanceof TypeResolverRegistryInterface) {
            $this->registry = new TypeResolverRegistry(
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(PropertyTypeResolverInterface::SERVICE_TAG)),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(MethodTypeResolverInterface::SERVICE_TAG))
            );
        }

        return $this->registry;
    }
}
