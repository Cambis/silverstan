<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Application;

use PHPStan\ShouldNotHappenException;
use SilverStripe\Core\DatabaselessKernel;
use function class_exists;

if (!class_exists(DatabaselessKernel::class)) {
    throw new ShouldNotHappenException('Could not find `silverstripe/framework`, did you forget to install?');
}

/**
 * @deprecated since 1.0.0
 */
final class SilverstanKernel extends DatabaselessKernel
{
    /**
     * @readonly
     */
    private bool $includeTestOnly;
    public function __construct(
        string $basePath,
        bool $includeTestOnly
    ) {
        $this->includeTestOnly = $includeTestOnly;
        parent::__construct($basePath);
    }

    protected function getIncludeTests(): bool
    {
        return $this->includeTestOnly;
    }
}
