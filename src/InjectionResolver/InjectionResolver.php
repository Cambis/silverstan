<?php

declare(strict_types=1);

namespace Cambis\Silverstan\InjectionResolver;

use SilverStripe\Core\Injector\Injector;

final class InjectionResolver
{
    /**
     * @template T
     * @param class-string<T> $className
     * @return T
     */
    public function create(string $className, mixed $argument = null): mixed
    {
        return Injector::inst()->create($className, $argument);
    }
}
