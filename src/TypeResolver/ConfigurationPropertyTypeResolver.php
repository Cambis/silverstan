<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use Cambis\Silverstan\Reflection\ReflectionResolver;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

final readonly class ConfigurationPropertyTypeResolver
{
    public function __construct(
        private ReflectionResolver $reflectionResolver,
    ) {
    }

    public function resolveConfigurationPropertyType(?ClassReflection $classReflection, string $propertyName): ?Type
    {
        $property = $this->reflectionResolver->resolveConfigurationProperty($classReflection, $propertyName);

        if (!$property instanceof PhpPropertyReflection) {
            return null;
        }

        /** @phpstan-ignore-next-line phpstanApi.method */
        return TypehintHelper::decideType($property->getNativeType(), $property->getPhpDocType());
    }
}
