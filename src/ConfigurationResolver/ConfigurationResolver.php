<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigCollectionFactoryInterface;
use PHPStan\Reflection\ReflectionProvider;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use function array_key_exists;
use function is_array;
use function is_string;
use function preg_match;

/**
 * This service provides access to the Silverstripe configuration API.
 *
 * @see \Cambis\Silverstan\Tests\ConfigurationResolver\ConfigurationResolverTest
 */
final class ConfigurationResolver
{
    /**
     * @readonly
     */
    private ConfigCollectionFactoryInterface $configCollectionFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * Source options bitmask value - do not exclude any middleware.
     *
     * @var int
     */
    public const EXCLUDE_NONE = 0;

    /**
     * Source options bitmask value - only get configuration set for this specific class, not any of its parents.
     *
     * @var int
     */
    public const EXCLUDE_INHERITED = 1;

    /**
     * Source options bitmask value - do not use class member configuration.
     *
     * @var int
     */
    public const EXCLUDE_PRIVATE_STATIC = 2;

    /**
     * Source options bitmask value - do not use additional statics sources (such as extension).
     *
     * @var int
     */
    public const EXCLUDE_EXTRA_SOURCES = 4;

    /**
     * @var string
     * @see https://regex101.com/r/ZXIMlR/1
     */
    private const EXTENSION_CLASSNAME_REGEX = '/^([^(]*)/';

    private ?ConfigCollectionInterface $configCollection = null;

    public function __construct(ConfigCollectionFactoryInterface $configCollectionFactory, ReflectionProvider $reflectionProvider)
    {
        $this->configCollectionFactory = $configCollectionFactory;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @param true|int-mask-of<self::EXCLUDE_*> $excludeMiddleware
     * @return mixed
     */
    public function get(string $className, ?string $name = null, $excludeMiddleware = 0)
    {
        if (!$this->configCollection instanceof ConfigCollectionInterface) {
            $this->configCollection = $this->configCollectionFactory->create();
        }

        return $this->configCollection->get($className, $name, $excludeMiddleware);
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

        // Safety check incase there is bad configuration
        if (!is_string($injectedClassName) || $injectedClassName === '') {
            return $className;
        }

        if (!$this->reflectionProvider->hasClass($injectedClassName)) {
            return $className;
        }

        return $injectedClassName;
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
}
