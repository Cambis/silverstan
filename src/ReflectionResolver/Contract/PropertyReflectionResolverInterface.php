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
    public const SERVICE_NAME = 'silverstan.reflectionResolver.propertyReflectionResolver';

    public function getConfigurationPropertyName(): string;

    /**
     * @return array<non-empty-string, PropertyReflection>
     */
    public function resolve(ClassReflection $classReflection): array;
}
