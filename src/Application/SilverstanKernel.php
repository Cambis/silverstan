<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Application;

use Override;
use PHPStan\ShouldNotHappenException;
use SilverStripe\Core\DatabaselessKernel;
use function class_exists;

/**
 * `DatabaselessKernel` does not exist in 6.x. Throw an exception and indicate to the user
 * that they need to opt in to the bleeding edge config.
 *
 * @phpstan-ignore classConstant.deprecatedClass
 */
if (!class_exists(DatabaselessKernel::class)) {
    throw new ShouldNotHappenException("\n
        The legacy autoloader is not supported on this version of Silverstripe.
        Opt-in to the bleeding edge config: https://github.com/Cambis/silverstan?tab=readme-ov-file#bleeding-edge in order to get support.
    ");
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
