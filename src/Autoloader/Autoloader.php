<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Autoloader;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use function spl_autoload_register;

final readonly class Autoloader
{
    public function __construct(
        private ClassManifest $classManifest
    ) {
    }

    /**
     * @internal
     */
    public function autoload(string $className): void
    {
        /** @var class-string $className */
        if (!$this->classManifest->classMap->hasClass($className)) {
            return;
        }

        require_once $this->classManifest->classMap->getClassPath($className);
    }

    public function register(): void
    {
        spl_autoload_register($this->autoload(...));
    }
}
