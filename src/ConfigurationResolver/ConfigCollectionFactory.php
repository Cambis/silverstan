<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\Finder\SilverstripeFileFinder;
use Composer\InstalledVersions;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\DependencyInjection\Container;
use PHPStan\Parser\Parser;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Collections\MemoryConfigCollection;
use SilverStripe\Config\Transformer\PrivateStaticTransformer;
use SilverStripe\Config\Transformer\YamlTransformer;
use function class_exists;
use function defined;
use function extension_loaded;
use function getenv;
use const BASE_PATH;

final class ConfigCollectionFactory
{
    private static ?ConfigCollectionInterface $cachedConfigCollection = null;

    public function __construct(
        private readonly Container $container,
        private readonly NodeFinder $nodeFinder,
        private readonly SilverstripeFileFinder $silverstripeFileFinder,
    ) {
    }

    /**
     * @api
     */
    public function create(): ConfigCollectionInterface
    {
        // Return a cached version is possible
        if (self::$cachedConfigCollection instanceof ConfigCollectionInterface) {
            return self::$cachedConfigCollection;
        }

        // Add Page/PageController stubs which may be required
        if (!class_exists('Page')) {
            require_once __DIR__ . '/../../stubs/Page.php';
        }

        if (!class_exists('PageController')) {
            require_once __DIR__ . '/../../stubs/PageController.php';
        }

        $configCollection = (new MemoryConfigCollection())->transform(
            [
                new PrivateStaticTransformer($this->resolveSilverstripeClassNames()),
                (new YamlTransformer(BASE_PATH, $this->silverstripeFileFinder->findConfigurationFiles()))
                    ->addRule('classexists', function (string $class): bool {
                        return class_exists($class);
                    })
                    ->addRule('envvarset', static function (string $var): bool {
                        return getenv($var) !== false;
                    })
                    ->addRule('constantdefined', function (string $const): bool {
                        return defined($const);
                    })
                    ->addRule('moduleexists', static function (string $module): bool {
                        if (!class_exists(InstalledVersions::class)) {
                            return true;
                        }

                        return InstalledVersions::isInstalled($module, true);
                    })
                    ->addRule('environment', static function (string $environment): bool {
                        return true;
                    })
                    ->addRule('envorconstant', static function (string $var): bool {
                        return getenv($var) !== false || defined($var);
                    })
                    ->addRule('extensionloaded', static function (string $extension) {
                        return extension_loaded($extension);
                    }),
            ]
        );

        self::$cachedConfigCollection = $configCollection;

        return $configCollection;
    }

    private function getParser(): Parser
    {
        /** @var Parser */
        return $this->container->getService('currentPhpVersionSimpleDirectParser');
    }

    /**
     * @return string[]
     */
    private function resolveSilverstripeClassNames(): array
    {
        $finder = $this->silverstripeFileFinder->findClassFiles();
        $classNames = [];

        foreach ($finder as $file) {
            $stmts = $this->getParser()->parseFile($file->getRealPath());
            $class = $this->nodeFinder->findFirstInstanceOf($stmts, Class_::class);

            if (!$class instanceof Class_) {
                continue;
            }

            if ($this->shouldSkipClass($class)) {
                continue;
            }

            $className = $class->namespacedName->toString();
            $classNames[] = $className;
        }

        return $classNames;
    }

    private function shouldSkipClass(Class_ $class): bool
    {
        if (!$class->namespacedName instanceof Name) {
            return true;
        }

        foreach ($class->implements as $interface) {
            if ($this->isNameTestOnly($interface) && !$this->shouldIncludeTests()) {
                return true;
            }
        }

        return false;
    }

    private function isNameTestOnly(Name $name): bool
    {
        return $name->toString() === 'SilverStripe\Dev\TestOnly';
    }

    private function shouldIncludeTests(): bool
    {
        return defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
    }
}
