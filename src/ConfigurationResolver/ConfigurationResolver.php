<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use PHPStan\Reflection\ReflectionProvider;
use SilverStripe\Config\Collections\ConfigCollectionInterface;
use function array_key_exists;
use function explode;
use function is_array;
use function preg_match;

final readonly class ConfigurationResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/ZXIMlR/1
     */
    private const EXTENSION_CLASSNAME_REGEX = '/^([^(]*)/';

    public function __construct(
        private ConfigCollectionInterface $configCollection,
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function get(string $className, string $name): mixed
    {
        return $this->configCollection->get($className, $name);
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
}
