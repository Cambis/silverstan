<?php

declare(strict_types=1);

namespace Cambis\Silverstan\PhpDoc\StubFilesExtension;

use Composer\InstalledVersions;
use OutOfBoundsException;
use PHPStan\PhpDoc\StubFilesExtension;
use Symfony\Component\Finder\Finder;
use function array_values;
use function class_exists;

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
    ];

    public function getFiles(): array
    {
        $files = [];
        $stubDirs = array_merge(self::COMMON_STUBS);
        if ($this->isInstalledVersion('silverstripe/versioned', 2)) {
            $stubDirs[] = __DIR__ . '/../../../stubs/SilverStripe/Versioned';
        }
        if ($this->isInstalledVersion('silverstripe/versioned-admin', 2)) {
            $stubDirs[] = __DIR__ . '/../../../stubs/SilverStripe/VersionedAdmin';
        }
        $stubFiles = Finder::create()->files()->name('*.stub')->in($stubDirs);
        foreach ($stubFiles as $stubFile) {
            $files[$stubFile->getRelativePathname()] = $stubFile->getRealPath();
        }
        return array_values($files);
    }

    private function isInstalledVersion(string $package, int $majorVersion): bool
    {
        if (!class_exists(InstalledVersions::class)) {
            return false;
        }

        try {
            $installedVersion = InstalledVersions::getVersion($package);
        } catch (OutOfBoundsException $exception) {
            return false;
        }

        return $installedVersion !== null && strncmp($installedVersion, $majorVersion . '.', strlen($majorVersion . '.')) === 0;
    }
}
