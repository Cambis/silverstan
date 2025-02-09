<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use SilverStripe\Config\Middleware\Middleware;

abstract class AbstractMiddleware implements Middleware
{
    public function __construct(
        /**
         * @var ConfigurationResolver::EXCLUDE_*
         */
        protected readonly int $disableFlag = ConfigurationResolver::EXCLUDE_NONE
    ) {
    }

    /**
     * @internal
     *
     * For backwards compatitability only.
     *
     * @return mixed[]
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * @internal
     *
     * For backwards compatitability only.
     *
     * @param mixed[] $data
     */
    public function __unserialize(array $data): void
    {
        // Noop
    }

    /**
     * @internal
     *
     * @deprecated for backwards compatitability only.
     *
     * @return string
     * @phpstan-ignore public.method.unused
     */
    public function serialize()
    {
        return '';
    }

    /**
     * @internal
     *
     * @deprecated for backwards compatitability only.
     *
     * @param mixed[] $data
     * @phpstan-return void
     * @phpstan-ignore public.method.unused
     */
    public function unserialize($data)
    {
        // Noop
    }

    /**
     * Check if this middlware is enabled
     *
     * @param true|int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware
     */
    final protected function enabled(true|int $excludeMiddleware): bool
    {
        if ($excludeMiddleware === true) {
            return false;
        }

        if ($this->disableFlag === ConfigurationResolver::EXCLUDE_NONE) {
            return true;
        }

        return ($excludeMiddleware & $this->disableFlag) !== $this->disableFlag;
    }
}
