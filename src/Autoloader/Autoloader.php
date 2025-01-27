<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Autoloader;

use Closure;
use Cambis\Silverstan\ClassManifest\ClassManifest;
use function file_exists;
use function spl_autoload_register;

final class Autoloader
{
    /**
     * @readonly
     */
    private ClassManifest $classManifest;
    public function __construct(ClassManifest $classManifest)
    {
        $this->classManifest = $classManifest;
    }

    /**
     * @phpstan-ignore-next-line public.method.unused
     */
    public function register(): void
    {
        spl_autoload_register(Closure::fromCallable([$this, 'autoload']));
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
