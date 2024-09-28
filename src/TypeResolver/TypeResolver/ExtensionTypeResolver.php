<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\InjectionResolver\InjectionResolver;
use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\MethodTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use function array_unique;
use function is_array;
use function preg_match;

final readonly class ExtensionTypeResolver implements MethodTypeResolverInterface, PropertyTypeResolverInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/ZXIMlR/1
     */
    private const EXTENSION_CLASSNAME_REGEX = '/^([^(]*)/';

    public function __construct(
        private ClassAnalyser $classAnalyser,
        private ConfigurationResolver $configurationResolver,
        private InjectionResolver $injectionResolver,
        private ReflectionProvider $reflectionProvider,
        private TypeFactory $typeFactory,
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'extensions';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classAnalyser->isExtensible($classReflection)) {
            return [];
        }

        $extensions = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName());

        if (!is_array($extensions) || $extensions === []) {
            return [];
        }

        /** @var string[] $extensions */
        $extensions = array_unique($extensions);
        $types = [];

        foreach ($extensions as $extension) {
            $extensionClassName = $this->resolveExtensionClassName($extension);

            if ($extensionClassName === null) {
                continue;
            }

            $classReflection = $this->reflectionProvider->getClass($extensionClassName);

            if (!$classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                continue;
            }

            $types[] = $this->typeFactory->createExtensibleTypeFromType(
                new ObjectType($extensionClassName, null, $classReflection)
            );
        }

        return $types;
    }

    private function resolveExtensionClassName(string $extensionName): ?string
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
