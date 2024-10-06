<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Finder;

use Composer\InstalledVersions;
use Symfony\Component\Finder\Finder;
use function class_exists;
use function is_dir;
use function is_string;
use function realpath;
use const BASE_PATH;

final class SilverstripeFileFinder
{
    public function findClassFiles(): Finder
    {
        $vendorFiles = Finder::create()
            ->files()
            ->name('/\.php$/')
            ->in($this->getSilverstripeClassDirectories())
            ->exclude('tests');

        $appFiles = Finder::create()
            ->files()
            ->name('/\.php$/')
            ->in(BASE_PATH)
            ->exclude('stubs')
            ->ignoreVCSIgnored(true);

        return $vendorFiles->append($appFiles);
    }

    public function findConfigurationFiles(): Finder
    {
        return Finder::create()
            ->files()
            ->name('/\.(yml|yaml)$/')
            ->in($this->getSilverstripeConfigurationDirectories());
    }

    /**
     * @return string[]
     */
    public function getSilverstripeClassDirectories(): array
    {
        if (!class_exists(InstalledVersions::class)) {
            return [];
        }

        $classDirs = [];

        $modules = InstalledVersions::getInstalledPackagesByType('silverstripe-vendormodule');

        foreach ($modules as $module) {
            $installPath = InstalledVersions::getInstallPath($module);

            if ($installPath === null) {
                continue;
            }

            $classPath = realpath($installPath);

            if (!is_string($classPath)) {
                continue;
            }

            if (!is_dir($classPath)) {
                continue;
            }

            $classDirs[] = $classPath;
        }

        return $classDirs;
    }

    /**
     * @return string[]
     */
    private function getSilverstripeConfigurationDirectories(): array
    {
        if (!class_exists(InstalledVersions::class)) {
            return [];
        }

        $configDirs = [];

        $modules = InstalledVersions::getInstalledPackagesByType('silverstripe-vendormodule');

        foreach ($modules as $module) {
            $installPath = InstalledVersions::getInstallPath($module);

            if ($installPath === null) {
                continue;
            }

            $rootConfigPath = realpath($installPath) . '/_config';
            $subDirConfigPath = realpath($installPath) . '/*/_config';

            if (is_dir($rootConfigPath)) {
                $configDirs[] = $rootConfigPath;
            }

            if (is_dir($subDirConfigPath)) {
                $configDirs[] = $subDirConfigPath;
            }
        }

        return $configDirs;
    }
}
