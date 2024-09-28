<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\Contract;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;

/**
 * This service is used to resolve the injected properties from a configuration property.
 *
 * ```
 * services:
 * 	-
 *		class: App\PHPStan\ReflectionResolver\MyPropertyReflectionResolver
 *		tags:
 *			- silverstan.reflectionResolver.propertyReflectionResolver
 * ```
 */
interface PropertyReflectionResolverInterface
{
    public function getConfigurationPropertyName(): string;

    /**
     * @return PropertyReflection[]
     */
    public function resolve(ClassReflection $classReflection): array;
}
