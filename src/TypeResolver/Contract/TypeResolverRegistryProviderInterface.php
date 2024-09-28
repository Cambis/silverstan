<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

interface TypeResolverRegistryProviderInterface
{
    public function getRegistry(): TypeResolverRegistryInterface;
}
