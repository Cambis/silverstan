<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\Finder\SilverstripeConfigurationFileFinder;
use Composer\InstalledVersions;
use PHPStan\Reflection\ReflectionProvider;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use SilverStripe\Config\Collections\MemoryConfigCollection;
use SilverStripe\Config\Collections\MutableConfigCollectionInterface;
use SilverStripe\Config\Transformer\PrivateStaticTransformer;
use SilverStripe\Config\Transformer\YamlTransformer;
use Symfony\Component\Finder\Finder;
use function array_key_exists;
use function class_exists;
use function defined;
use function explode;
use function extension_loaded;
use function getenv;
use function is_array;
use function preg_match;

final class ConfigurationResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/ZXIMlR/1
     */
    private const EXTENSION_CLASSNAME_REGEX = '/^([^(]*)/';

    private ?MutableConfigCollectionInterface $configCollection = null;

    private ?Finder $finder = null;

    /**
     * @var class-string[]
     */
    private array $classes = [];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly SilverstripeConfigurationFileFinder $silverstripeConfigurationFinder,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function get(string $className, string $name): mixed
    {
        if ($this->getConfigCollection()->exists($className, $name)) {
            return $this->getConfigCollection()->get($className, $name);
        }

        $this->generateConfigForClass($className);

        return $this->getConfigCollection()->get($className, $name);
    }

    public function resolveClassName(string $className): string
    {
        $classConfig = $this->get('SilverStripe\Core\Injector\Injector', $className);

        if (!is_array($classConfig)) {
            return $className;
        }

        if (!array_key_exists('class', $classConfig)) {
            return $className;
        }

        $injectedClassName = $classConfig['class'];

        if (!$this->reflectionProvider->hasClass($injectedClassName)) {
            return $className;
        }

        return $injectedClassName;
    }

    public function resolveDotNotation(string $fieldType): string
    {
        [$class] = explode('.', $fieldType, 2);

        return $class;
    }

    public function resolveExtensionClassName(string $extensionName): ?string
    {
        $matches = [];

        if (preg_match(self::EXTENSION_CLASSNAME_REGEX, $extensionName, $matches) === false) {
            return null;
        }

        $resolved = $matches[1];

        if (!$this->reflectionProvider->hasClass($resolved)) {
            return null;
        }

        return $this->resolveClassName($resolved);
    }

    /**
     * Generate a config entry for a given class.
     *
     * @param class-string $className
     * @see https://github.com/silverstripe/silverstripe-config/blob/2/docs/usage.md
     */
    private function generateConfigForClass(string $className): void
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return;
        }

        if (!array_key_exists($className, $this->classes)) {
            $this->classes[] = $className;
        }

        if (!$this->finder instanceof Finder) {
            $this->finder = $this->silverstripeConfigurationFinder->findConfigurationFiles();
        }

        (new PrivateStaticTransformer($this->classes))
            ->transform($this->getConfigCollection());

        (new YamlTransformer(__DIR__ . '/../../', $this->finder))
            ->addRule('classexists', function (string $class): bool {
                return $this->reflectionProvider->hasClass($class);
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
            })
            ->transform($this->getConfigCollection());
    }

    private function getConfigCollection(): MutableConfigCollectionInterface
    {
        if (!$this->configCollection instanceof ConfigCollectionInterface) {
            $this->configCollection = new MemoryConfigCollection();
        }

        return $this->configCollection;
    }
}
