<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\Contract;

interface ReflectionResolverRegistryProviderInterface
{
    public function getRegistry(): ReflectionResolverRegistryInterface;
}
