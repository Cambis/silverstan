<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Application;

use Override;
use SilverStripe\Core\DatabaselessKernel;

final class SilverstanKernel extends DatabaselessKernel
{
    public function __construct(
        string $basePath,
        private readonly bool $includeTestOnly
    ) {
        parent::__construct($basePath);
    }

    #[Override]
    protected function getIncludeTests(): bool
    {
        return $this->includeTestOnly;
    }
}
