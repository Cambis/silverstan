<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Finder;

use Composer\InstalledVersions;
use Symfony\Component\Finder\Finder;
use function class_exists;
use function is_dir;
use function realpath;

final class SilverstripeConfigurationFileFinder
{
    public function findConfigurationFiles(): Finder
    {
        $moduleDirs = [
            ...$this->getSilverstripeModuleDirectories(),
        ];

        return Finder::create()
            ->files()
            ->name('/\.(yml|yaml)$/')
            ->in($moduleDirs);
    }

    /**
     * @return string[]
     */
    private function getSilverstripeModuleDirectories(): array
    {
        if (!class_exists(InstalledVersions::class)) {
            return [];
        }

        $moduleDirs = [];

        $modules = InstalledVersions::getInstalledPackagesByType('silverstripe-vendormodule');

        foreach ($modules as $module) {
            $installPath = InstalledVersions::getInstallPath($module);

            if ($installPath === null) {
                continue;
            }

            $rootConfigPath = realpath($installPath) . '/_config';
            $subDirConfigPath = realpath($installPath) . '/*/_config';

            if (is_dir($rootConfigPath)) {
                $moduleDirs[] = $rootConfigPath;
            }

            if (is_dir($subDirConfigPath)) {
                $moduleDirs[] = $subDirConfigPath;
            }
        }

        return $moduleDirs;
    }
}
