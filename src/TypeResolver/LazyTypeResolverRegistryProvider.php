<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

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
                $this->container->getByType(TypeResolver::class),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag('silverstan.typeResolver.propertyTypeResolver')),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag('silverstan.typeResolver.methodTypeResolver'))
            );
        }

        return $this->registry;
    }
}
