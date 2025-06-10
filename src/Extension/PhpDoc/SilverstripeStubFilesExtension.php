<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\PhpDoc;

use PHPStan\PhpDoc\StubFilesExtension;
use Symfony\Component\Finder\Finder;

final class SilverstripeStubFilesExtension implements StubFilesExtension
{
    public function getFiles(): array
    {
        $files = [];
        $stubFiles = Finder::create()->files()->name('*.stub')->in(__DIR__ . '/../../../stubs');
        foreach ($stubFiles as $stubFile) {
            $files[] = $stubFile->getRealPath();
        }
        return $files;
    }
}
