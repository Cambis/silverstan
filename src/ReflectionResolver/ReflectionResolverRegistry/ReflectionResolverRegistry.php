<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolverRegistry;

use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryInterface;

final class ReflectionResolverRegistry implements ReflectionResolverRegistryInterface
{
    /**
     * @readonly
     */
    private array $propertyReflectionResolvers;
    /**
     * @readonly
     */
    private array $methodReflectionTypeResolvers;
    public function __construct(array $propertyReflectionResolvers, array $methodReflectionTypeResolvers)
    {
        /**
         * @var PropertyReflectionResolverInterface[]
         */
        $this->propertyReflectionResolvers = $propertyReflectionResolvers;
        /**
         * @var MethodReflectionResolverInterface[]
         */
        $this->methodReflectionTypeResolvers = $methodReflectionTypeResolvers;
    }

    public function getPropertyReflectionResolvers(): array
    {
        return $this->propertyReflectionResolvers;
    }

    public function getMethodReflectionResolvers(): array
    {
        return $this->methodReflectionTypeResolvers;
    }
}
