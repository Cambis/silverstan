<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\PhpDoc;

use Override;
use PHPStan\PhpDoc\StubFilesExtension;
use Symfony\Component\Finder\Finder;
use function array_values;

/**
 * Inspired by https://github.com/larastan/larastan/blob/main/src/LarastanStubFilesExtension.php
 */
final class SilverstripeStubFilesExtension implements StubFilesExtension
{
    #[Override]
    public function getFiles(): array
    {
        $files = [];
        $stubDirs = [__DIR__ . '/../../../stubs'];
        $stubFiles = Finder::create()->files()->name('*.stub')->in($stubDirs);

        foreach ($stubFiles as $stubFile) {
            $files[$stubFile->getRelativePathname()] = $stubFile->getRealPath();
        }

        return array_values($files);
    }
}
