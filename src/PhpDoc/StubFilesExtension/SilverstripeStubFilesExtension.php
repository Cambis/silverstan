<?php

declare(strict_types=1);

namespace Cambis\Silverstan\PhpDoc\StubFilesExtension;

use Override;
use PHPStan\PhpDoc\StubFilesExtension;
use Symfony\Component\Finder\Finder;
use function array_values;

/**
 * Inspired by https://github.com/larastan/larastan/blob/2.x/src/LarastanStubFilesExtension.php
 */
final class SilverstripeStubFilesExtension implements StubFilesExtension
{
    /**
     * @var string[]
     */
    private const COMMON_STUBS = [
        __DIR__ . '/../../../stubs/Psr',
        __DIR__ . '/../../../stubs/SilverStripe/Config',
        __DIR__ . '/../../../stubs/SilverStripe/Control',
        __DIR__ . '/../../../stubs/SilverStripe/Core',
        __DIR__ . '/../../../stubs/SilverStripe/Dev',
        __DIR__ . '/../../../stubs/SilverStripe/includes',
        __DIR__ . '/../../../stubs/SilverStripe/ORM',
        __DIR__ . '/../../../stubs/SilverStripe/Security',
        __DIR__ . '/../../../stubs/SilverStripe/Versioned',
        __DIR__ . '/../../../stubs/SilverStripe/VersionedAdmin',
    ];

    #[Override]
    public function getFiles(): array
    {
        $files = [];
        $stubDirs = [...self::COMMON_STUBS];

        $stubFiles = Finder::create()->files()->name('*.stub')->in($stubDirs);

        foreach ($stubFiles as $stubFile) {
            $files[$stubFile->getRelativePathname()] = $stubFile->getRealPath();
        }

        return array_values($files);
    }
}
