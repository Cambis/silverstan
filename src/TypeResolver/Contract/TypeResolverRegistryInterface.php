<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

interface TypeResolverRegistryInterface
{
    /**
     * @return PropertyTypeResolverInterface[]
     */
    public function getPropertyTypeResolvers(): array;

    /**
     * @return MethodTypeResolverInterface[]
     */
    public function getMethodTypeResolvers(): array;
}
