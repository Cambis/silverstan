<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ModuleFinder;

use Composer\InstalledVersions;
use Symfony\Component\Finder\Finder;
use function dirname;
use function realpath;

final class ModuleFinder
{
    /**
     * @var ?list<string>
     */
    private ?array $excludedDirectories = null;

    /**
     * @var ?list<string>
     */
    private ?array $appDirectories = null;

    /**
     * @var ?list<string>
     */
    private ?array $vendorModuleDirectories = null;

    /**
     * @var ?list<string>
     */
    private ?array $vendorModuleRootDirectories = null;

    public function __construct(
        private readonly bool $includeTestOnly
    ) {
    }

    public function getPhpFiles(): Finder
    {
        return Finder::create()
            ->in($this->getIncludedDirectories())
            ->files()
            ->name('*.php')
            ->notName(['index.php', 'cli-script.php'])
            ->notPath($this->getExcludedDirectories());
    }

    public function getYamlConfigFiles(): Finder
    {
        return Finder::create()
            ->in([
                ...$this->getAppDirectories(),
                ...$this->getVendorModuleRootDirectories(),
            ])
            ->files()
            ->path('/\_config/')
            ->notPath($this->getExcludedDirectories())
            ->name(['*.yml', '*.yaml'])
            ->depth('< 2');
    }

    /**
     * Get a list of directories that are discoverable by the manifest.
     *
     * @return list<string>
     */
    public function getIncludedDirectories(): array
    {
        return [
            ...$this->getVendorModuleDirectories(),
            ...$this->getAppDirectories(),
        ];
    }

    /**
     * Get the root directory of the Silverstripe application.
     */
    public function getAppRootDirectory(): string
    {
        foreach (InstalledVersions::getAllRawData() as $data) {
            if (!isset($data['versions']['silverstripe/framework'])) {
                continue;
            }

            $path = realpath($data['root']['install_path']);

            return $path !== false ? $path : $data['root']['install_path'];
        }

        $root = InstalledVersions::getRootPackage();
        $path = realpath($root['install_path']);

        return $path !== false ? $path : $root['install_path'];
    }

    /**
     * Get a list of directories that are not discoverable by the manifest.
     *
     * @return list<string>
     */
    private function getExcludedDirectories(): array
    {
        $excludedNames = ['_manifest_exclude', 'lang', 'thirdparty'];

        if (!$this->includeTestOnly) {
            $excludedNames[] = 'tests';
        }

        if ($this->excludedDirectories !== null) {
            return $this->excludedDirectories;
        }

        $hits = Finder::create()
            ->in([
                ...$this->getVendorModuleDirectories(),
                ...$this->getAppDirectories(),
            ])
            ->directories()
            ->name($excludedNames);

        $excludedDirs = [];

        foreach ($hits as $hit) {
            $excludedDirs[] = $hit->getRealPath() !== false ? dirname($hit->getRealPath()) : dirname($hit->getPath());
        }

        $this->excludedDirectories = $excludedDirs;

        return $this->excludedDirectories;
    }

    /**
     * @return list<string>
     */
    private function getVendorModuleRootDirectories(): array
    {
        if ($this->vendorModuleRootDirectories !== null) {
            return $this->vendorModuleRootDirectories;
        }

        $installedVersions = InstalledVersions::getInstalledPackagesByType('silverstripe-vendormodule');
        $vendorModuleRootDirs = [];

        foreach ($installedVersions as $packageName) {
            $installPath = InstalledVersions::getInstallPath($packageName);

            if ($installPath === null) {
                continue;
            }

            if (realpath($installPath) !== false) {
                $installPath = realpath($installPath);
            }

            $vendorModuleRootDirs[] = $installPath;
        }

        $this->vendorModuleRootDirectories = $vendorModuleRootDirs;

        return $this->vendorModuleRootDirectories;
    }

    /**
     * Get a list of `silverstripe-vendormodule` directories.
     *
     * @return list<string>
     */
    private function getVendorModuleDirectories(): array
    {
        if ($this->vendorModuleDirectories !== null) {
            return $this->vendorModuleDirectories;
        }

        $hits = Finder::create()
            ->in($this->getVendorModuleRootDirectories())
            ->directories()
            ->notPath(['lang', 'tests', 'thirdparty']);

        $this->vendorModuleDirectories = [];

        foreach ($hits as $hit) {
            $this->vendorModuleDirectories[] = $hit->getPathname();
        }

        return $this->vendorModuleDirectories;
    }

    /**
     * Get a list of directories from the application root that contain the `_config` directory or the `config.php` file.
     *
     * @return list<string>
     */
    private function getAppDirectories(): array
    {
        if ($this->appDirectories !== null) {
            return $this->appDirectories;
        }

        $configFiles = Finder::create()
            ->in($this->getAppRootDirectory())
            ->files()
            ->exclude('vendor')
            ->name('_config.php');

        $configDirs = Finder::create()
            ->in($this->getAppRootDirectory())
            ->directories()
            ->exclude('vendor')
            ->name('_config');

        $appDirs = [];

        foreach ($configFiles as $configFile) {
            $appDirs[] = $configFile->getRealPath() !== false ? dirname($configFile->getRealPath()) : dirname($configFile->getPath());
        }

        foreach ($configDirs as $configDir) {
            $appDirs[] = $configDir->getRealPath() !== false ? dirname($configDir->getRealPath()) : dirname($configDir->getPath());
        }

        $this->appDirectories = $appDirs;

        return $this->appDirectories;
    }
}