<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver\Middleware;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use SilverStripe\Config\Middleware\Middleware;

abstract class AbstractMiddleware implements Middleware
{
    /**
     * @readonly
     */
    protected int $disableFlag = ConfigurationResolver::EXCLUDE_NONE;
    public function __construct(int $disableFlag = ConfigurationResolver::EXCLUDE_NONE)
    {
        /**
         * @var ConfigurationResolver::EXCLUDE_*
         */
        $this->disableFlag = $disableFlag;
    }

    /**
     * Check if this middlware is enabled
     *
     * @param true|int-mask-of<ConfigurationResolver::EXCLUDE_*> $excludeMiddleware
     */
    final protected function enabled($excludeMiddleware): bool
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
