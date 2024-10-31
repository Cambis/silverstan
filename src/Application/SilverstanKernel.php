<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Application;

use Override;
use SilverStripe\Core\CoreKernel;

final class SilverstanKernel extends CoreKernel
{
    public function __construct(
        string $basePath,
        private readonly bool $includeTestOnly
    ) {
        parent::__construct($basePath);

        $this->setBootDatabase(false);
    }

    #[Override]
    protected function getIncludeTests(): bool
    {
        return $this->includeTestOnly;
    }
}
