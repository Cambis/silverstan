<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Application;

use Override;
use PHPStan\ShouldNotHappenException;
use SilverStripe\Core\DatabaselessKernel;
use function class_exists;

/**
 * @phpstan-ignore classConstant.deprecatedClass
 */
if (!class_exists(DatabaselessKernel::class)) {
    throw new ShouldNotHappenException('Could not find `silverstripe/framework`, did you forget to install?');
}

/**
 * @deprecated since 1.0.0
 * @phpstan-ignore-next-line classConstant.deprecatedClass
 */
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
