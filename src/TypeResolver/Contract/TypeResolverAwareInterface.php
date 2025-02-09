<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

use Cambis\Silverstan\TypeResolver\TypeResolver;

interface TypeResolverAwareInterface
{
    /**
     * @return static
     */
    public function setTypeResolver(TypeResolver $typeResolver);
}
