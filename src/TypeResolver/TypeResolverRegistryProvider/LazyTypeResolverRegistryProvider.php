<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolverRegistryProvider;

use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryProviderInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Cambis\Silverstan\TypeResolver\TypeResolverRegistry\TypeResolverRegistry;
use Override;
use PHPStan\DependencyInjection\Container;
use function array_reverse;

final class LazyTypeResolverRegistryProvider implements TypeResolverRegistryProviderInterface
{
    /**
     * @readonly
     */
    private Container $container;
    private ?TypeResolverRegistryInterface $registry = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    #[Override]
    public function getRegistry(): TypeResolverRegistryInterface
    {
        if (!$this->registry instanceof TypeResolverRegistryInterface) {
            $this->registry = new TypeResolverRegistry(
                $this->container->getByType(TypeResolver::class),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(PropertyTypeResolverInterface::SERVICE_NAME)),
                /** @phpstan-ignore-next-line argument.type */
                array_reverse($this->container->getServicesByTag(MethodTypeResolverInterface::SERVICE_NAME))
            );
        }

        return $this->registry;
    }
}
