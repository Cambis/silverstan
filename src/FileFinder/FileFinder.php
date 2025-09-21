<?php

declare(strict_types=1);

namespace Cambis\Silverstan\FileFinder;

use Composer\InstalledVersions;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use function array_unique;
use function dirname;
use function file_exists;
use function realpath;
use function sha1;
use function sprintf;

final class FileFinder
{
    /**
     * @readonly
     */
    private bool $includeTestOnly;
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

    public function __construct(bool $includeTestOnly)
    {
        $this->includeTestOnly = $includeTestOnly;
    }

    public function getPhpFiles(): Finder
    {
        $app = Finder::create()
            ->in($this->getAppDirectories())
            ->files()
            ->name('*.php')
            ->notName(['index.php', 'cli-script.php'])
            ->notPath(array_merge($this->getExcludedDirectories(), ['vendor']));

        // Skip if there are no found vendor modules
        if ($this->getVendorModuleDirectories() === []) {
            return $app;
        }

        $vendor = Finder::create()
            ->in($this->getVendorModuleDirectories())
            ->files()
            ->name('*.php')
            ->notName(['index.php', 'cli-script.php'])
            ->notPath($this->getExcludedDirectories());

        return $app->append($vendor);
    }

    public function getYamlConfigFiles(): Finder
    {
        return Finder::create()
            ->in(array_merge($this->getAppDirectories(), $this->getVendorModuleRootDirectories()))
            ->files()
            ->path('/\_config\//')
            ->notPath($this->getExcludedDirectories())
            ->name(['*.yml', '*.yaml'])
            ->depth('< 2');
    }

    /**
     * Get the root directory of the Silverstripe application.
     */
    public function getAppRootDirectory(): string
    {
        // Check for silverstripe/framework
        $path = $this->getPackageInstallPath('silverstripe/framework');

        if ($path !== null) {
            return $path;
        }

        // Fallback to silverstripe/config
        $path = $this->getPackageInstallPath('silverstripe/config');

        if ($path !== null) {
            return $path;
        }

        // Final fallback to root package
        $root = InstalledVersions::getRootPackage();
        $path = realpath($root['install_path']);

        return $path !== false ? $path : $root['install_path'];
    }

    /**
     * Generate a cache key determined by the last modified time of yaml files.
     *
     * @return non-empty-string
     */
    public function getYamlConfigCacheKey(): string
    {
        $configFiles = $this->getYamlConfigFiles()
            ->sortByModifiedTime()
            ->reverseSorting();

        if (!$configFiles->hasResults()) {
            return sha1('config');
        }

        return sha1(sprintf(
            'config-%s',
            $configFiles->getIterator()->current()->getMTime(),
        ));
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

        $inDirs = array_merge($this->getVendorModuleDirectories(), $this->getAppDirectories());

        // Skip if no directories
        if ($inDirs === []) {
            return [];
        }

        $hits = Finder::create()
            ->in($inDirs)
            ->directories()
            ->name($excludedNames);

        $this->excludedDirectories = $this->getFinderDirectories($hits);

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

            // Prevent double ups
            if ($installPath === $this->getAppRootDirectory()) {
                continue;
            }

            if (realpath($installPath) !== false) {
                /** @var non-empty-string $installPath */
                $installPath = realpath($installPath);
            }

            // Check again, prevent double ups
            if ($installPath === $this->getAppRootDirectory()) {
                continue;
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

        // Skip if no root directories
        if ($this->getVendorModuleRootDirectories() === []) {
            return [];
        }

        $hits = Finder::create()
            ->in($this->getVendorModuleRootDirectories())
            ->directories()
            ->filter(static function (SplFileInfo $splFileInfo): bool {
                return file_exists($splFileInfo->getPathname());
            })
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
     * If the application root is inside a `silverstripe-vendormodule` return the directories inside it.
     *
     * @return list<string>
     */
    private function getAppDirectories(): array
    {
        if ($this->appDirectories !== null) {
            return $this->appDirectories;
        }

        $appDirs = [];

        // Modules don't need `_config` directory or `_config.php` file
        if ($this->isInModule()) {
            $moduleDirectories = Finder::create()
                ->in($this->getAppRootDirectory())
                ->directories()
                ->exclude(['node_modules', 'vendor']);

            $appDirs = $this->getFinderDirectories($moduleDirectories);
        } else {
            $configFiles = Finder::create()
                ->in($this->getAppRootDirectory())
                ->files()
                ->exclude(['node_modules', 'vendor'])
                ->name('_config.php');

            $configDirs = Finder::create()
                ->in($this->getAppRootDirectory())
                ->directories()
                ->exclude(['node_modules', 'vendor'])
                ->name('_config');

            $appDirs = array_merge($this->getFinderDirectories($configFiles), $this->getFinderDirectories($configDirs));
        }

        $this->appDirectories = [...array_unique($appDirs)];

        return $this->appDirectories;
    }

    private function getPackageInstallPath(string $packageName): ?string
    {
        foreach (InstalledVersions::getAllRawData() as $data) {
            if (!isset($data['versions'][$packageName])) {
                continue;
            }

            $path = realpath($data['root']['install_path']);

            return $path !== false ? $path : $data['root']['install_path'];
        }

        return null;
    }

    /**
     * True if the app root is inside a `silverstripe-vendormodule`.
     */
    private function isInModule(): bool
    {
        $installedVersions = InstalledVersions::getInstalledPackagesByType('silverstripe-vendormodule');

        foreach ($installedVersions as $packageName) {
            $path = InstalledVersions::getInstallPath($packageName);

            if ($path === null) {
                continue;
            }

            $path = realpath($path);

            if ($path !== $this->getAppRootDirectory()) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function getFinderDirectories(Finder $finder): array
    {
        $dirs = [];

        foreach ($finder as $directory) {
            $dirs[] = $directory->getRealPath() !== false ? dirname($directory->getRealPath()) : dirname($directory->getPath());
        }

        return $dirs;
    }
}
