<?php

declare(strict_types=1);

namespace Cambis\Silverstan\InjectionResolver;

use Exception;
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

    /**
     * Resolve the class name with the Injector, as it may have been replaced.
     *
     * @param class-string $className
     */
    public function resolveInjectedClassName(string $className): string
    {
        try {
            return $this->create($className)::class;
        } catch (Exception) {
        }

        // Fallback case
        return $className;
    }
}
