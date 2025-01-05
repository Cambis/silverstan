<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolverRegistry;

use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverRegistryInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;

final readonly class TypeResolverRegistry implements TypeResolverRegistryInterface
{
    public function __construct(
        TypeResolver $typeResolver,
        /**
         * @var PropertyTypeResolverInterface[]
         */
        private array $propertyTypeResolvers,
        /**
         * @var MethodTypeResolverInterface[]
         */
        private array $methodTypeResolvers
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
