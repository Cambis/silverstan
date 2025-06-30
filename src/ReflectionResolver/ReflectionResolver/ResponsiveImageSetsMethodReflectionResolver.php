<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Reflection\MethodReflection\ExtensibleMethodReflection;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\ObjectType;
use function array_keys;
use function is_array;
use function strtolower;

/**
 * Resolves magic sets methods from `Heyday\ResponsiveImages\ResponsiveImageExtension`.
 *
 * @see \Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension\ExtensibleClassReflectionExtensionTest
 */
final readonly class ResponsiveImageSetsMethodReflectionResolver implements MethodReflectionResolverInterface
{
    public function __construct(
        private ConfigurationResolver $configurationResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'sets';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        $sets = $this->configurationResolver->get('Heyday\ResponsiveImages\ResponsiveImageExtension', $this->getConfigurationPropertyName());

        // No sets likely means this extension is not installed
        if (!is_array($sets) || $sets === []) {
            return [];
        }

        if (!$classReflection->is('SilverStripe\Assets\Image') && !$classReflection->is('SilverStripe\Assets\Storage\DBFile')) {
            return [];
        }

        $methodReflections = [];

        $returnType = new ObjectType('SilverStripe\ORM\FieldType\DBHTMLText');

        /** @var non-empty-string $set */
        foreach (array_keys($sets) as $set) {
            $methodReflections[strtolower($set)] = new ExtensibleMethodReflection($set, $classReflection, $returnType, [], false, false, null, TemplateTypeMap::createEmpty());
        }

        return $methodReflections;
    }
}
