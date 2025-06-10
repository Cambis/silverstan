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

        $path = $this->classManifest->getClassPath($className);

        // Safety check
        if (!file_exists($path)) {
            return;
        }

        (static function (string $path): void { require $path; })($path);
    }
}
