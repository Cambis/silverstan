<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryInterface;
use Override;

final class TypeResolverRegistry implements TypeResolverRegistryInterface
{
    public function __construct(
        TypeResolver $typeResolver,
        /**
         * @var PropertyTypeResolverInterface[]
         */
        private readonly array $propertyTypeResolvers,
        /**
         * @var MethodTypeResolverInterface[]
         */
        private readonly array $methodTypeResolvers
    ) {
        foreach ($propertyTypeResolvers as $propertyTypeResolver) {
            if (!$propertyTypeResolver instanceof TypeResolverAwareInterface) {
                continue;
            }

            $propertyTypeResolver->setTypeResolver($typeResolver);
        }

        foreach ($methodTypeResolvers as $methodTypeResolver) {
            if (!$methodTypeResolver instanceof TypeResolverAwareInterface) {
                continue;
            }

            $methodTypeResolver->setTypeResolver($typeResolver);
        }
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
