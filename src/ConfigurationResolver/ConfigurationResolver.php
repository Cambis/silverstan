<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\ConfigurationResolver\Contract\ConfigCollectionFactoryInterface;
use PHPStan\Reflection\ReflectionProvider;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use function array_key_exists;
use function explode;
use function is_array;
use function preg_match;

/**
 * This service provides access to the Silverstripe configuration API.
 */
final class ConfigurationResolver
{
    /**
     * Source options bitmask value - only get configuration set for this specific class, not any of it's parents.
     *
     * @var int
     */
    public const UNINHERITED = 1;

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

    public function __construct(
        private readonly ConfigCollectionFactoryInterface $configCollectionFactory,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function get(string $className, ?string $name = null, true|int $excludeMiddleware = 0): mixed
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

    public function resolvePrefixNotation(string $fieldType): string
    {
        [$_, $class] = explode('%$', $fieldType, 2);

        return $class;
    }
}
