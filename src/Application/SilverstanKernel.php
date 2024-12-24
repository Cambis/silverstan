<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Application;

use Override;
use PHPStan\ShouldNotHappenException;
use SilverStripe\Core\DatabaselessKernel;
use function class_exists;

if (!class_exists(DatabaselessKernel::class)) {
    throw new ShouldNotHappenException('Could not find `silverstripe/framework`, did you forget to install?');
}

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
