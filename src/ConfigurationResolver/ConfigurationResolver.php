<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ConfigurationResolver;

use Cambis\Silverstan\InjectionResolver\InjectionResolver;
use PHPStan\Reflection\ReflectionProvider;
use SilverStripe\Core\Config\Config;
use function explode;
use function preg_match;

final readonly class ConfigurationResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/ZXIMlR/1
     */
    private const EXTENSION_CLASSNAME_REGEX = '/^([^(]*)/';

    public function __construct(
        private InjectionResolver $injectionResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @param class-string $className
     */
    public function get(string $className, string $name): mixed
    {
        return Config::inst()->get($className, $name, Config::EXCLUDE_EXTRA_SOURCES | Config::UNINHERITED);
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

        return $this->injectionResolver->resolveInjectedClassName($resolved);
    }
}
