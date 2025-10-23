<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Cambis\Silverstan\TypeResolver\Contract\LazyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\TypeCombinator;
use function array_filter;
use function array_map;
use function array_values;
use function in_array;
use function is_array;

/**
 * This resolver tracks extension owners and saves them in a meta property `__getOwners`.
 */
final readonly class ExtensionOwnerMetaPropertyTypeResolver implements PropertyTypeResolverInterface, LazyTypeResolverInterface
{
    public function __construct(
        private ConfigurationResolver $configurationResolver,
        private ClassManifest $classManifest,
        private ReflectionProvider $reflectionProvider,
        private TypeFactory $typeFactory
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return '__silverstan_owners';
    }

    /**
     * @phpstan-ignore-next-line return.unusedType
     */
    #[Override]
    public function getExcludeMiddleware(): true|int
    {
        return ConfigurationResolver::EXCLUDE_INHERITED | ConfigurationResolver::EXCLUDE_EXTRA_SOURCES;
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->reflectionProvider->hasClass('SilverStripe\Core\Extension')) {
            return [];
        }

        if (!$classReflection->isSubclassOfClass($this->reflectionProvider->getClass('SilverStripe\Core\Extension'))) {
            return [];
        }

        // Loop over class manifest and find owners of this extension
        $owners = array_filter(array_values($this->classManifest->getClasses()), function (string $owner) use ($classReflection): bool {
            $extensions = $this->configurationResolver->get($owner, 'extensions', $this->getExcludeMiddleware());

            if (!is_array($extensions) || $extensions === []) {
                return false;
            }

            /**
             * @var array<class-string|null> $extensions
             * @var array<class-string> $extensionsFiltered
             */
            $extensionsFiltered = array_filter(array_values($extensions), static function (?string $value): bool {
                return $value !== null;
            });

            // Use the Injector to resolve the extension class name as it may have been replaced
            return in_array(
                $classReflection->getName(),
                array_map($this->configurationResolver->resolveClassName(...), $extensionsFiltered),
                true
            );
        });

        // Type to represent the extension itself
        $staticType = $this->typeFactory->createExtensibleTypeFromType(new StaticType($classReflection));

        // No owners
        if ($owners === []) {
            return [
                '__getOwners' => new GenericObjectType($classReflection->getParentClassesNames()[0], [$staticType]),
            ];
        }

        $types = [];

        foreach ($owners as $owner) {
            $types[] = TypeCombinator::intersect(
                $this->typeFactory->createExtensibleTypeFromType(new ObjectType($owner)),
                $staticType
            );
        }

        return [
            '__getOwners' => new GenericObjectType($classReflection->getParentClassesNames()[0], [TypeCombinator::union(...$types)]),
        ];
    }
}
