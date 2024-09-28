<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\Contract;

interface ReflectionResolverRegistryInterface
{
    /**
     * @return PropertyReflectionResolverInterface[]
     */
    public function getPropertyReflectionResolvers(): array;

    /**
     * @return MethodReflectionResolverInterface[]
     */
    public function getMethodReflectionResolvers(): array;
}
