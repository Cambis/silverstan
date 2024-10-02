<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

use Cambis\Silverstan\TypeResolver\TypeResolver;

interface TypeResolverAwareInterface
{
    public function setTypeResolver(TypeResolver $typeResolver): static;
}
