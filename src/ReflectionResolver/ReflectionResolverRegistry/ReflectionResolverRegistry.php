<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolverRegistry;

use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\ReflectionResolver\Contract\ReflectionResolverRegistryInterface;
use Override;

final readonly class ReflectionResolverRegistry implements ReflectionResolverRegistryInterface
{
    public function __construct(
        /**
         * @var PropertyReflectionResolverInterface[]
         */
        private array $propertyReflectionResolvers,
        /**
         * @var MethodReflectionResolverInterface[]
         */
        private array $methodReflectionTypeResolvers
    ) {
    }

    #[Override]
    public function getPropertyReflectionResolvers(): array
    {
        return $this->propertyReflectionResolvers;
    }

    #[Override]
    public function getMethodReflectionResolvers(): array
    {
        return $this->methodReflectionTypeResolvers;
    }
}
