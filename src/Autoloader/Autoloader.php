<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Autoloader;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use function file_exists;
use function spl_autoload_register;

final readonly class Autoloader
{
    public function __construct(
        private ClassManifest $classManifest
    ) {
    }

    /**
     * @phpstan-ignore-next-line public.method.unused
     */
    public function register(): void
    {
        spl_autoload_register($this->autoload(...));
    }

    private function autoload(string $className): void
    {
        /** @var class-string $className */
        if (!$this->classManifest->hasClass($className)) {
            return;
        }

        // Safety check
        if (!file_exists($this->classManifest->getClassPath($className))) {
            return;
        }

        require_once $this->classManifest->getClassPath($className);
    }
}
