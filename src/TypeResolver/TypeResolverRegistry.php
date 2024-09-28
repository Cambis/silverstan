<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryInterface;
use Override;

final readonly class TypeResolverRegistry implements TypeResolverRegistryInterface
{
    public function __construct(
        /**
         * @var PropertyTypeResolverInterface[]
         */
        private array $propertyTypeResolvers,
        /**
         * @var MethodTypeResolverInterface[]
         */
        private array $methodTypeResolvers
    ) {
    }

    #[Override]
    public function getPropertyTypeResolvers(): array
    {
        return $this->propertyTypeResolvers;
    }

    #[Override]
    public function getMethodTypeResolvers(): array
    {
        return $this->methodTypeResolvers;
    }
}
