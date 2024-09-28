<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\Contract;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;

/**
 * This service is used to resolve the injected methods from a configuration property.
 *
 * ```
 * services:
 * 	-
 *		class: App\PHPStan\ReflectionResolver\MyMethodReflectionResolver
 *		tags:
 *			- silverstan.reflectionResolver.methodReflectionResolver
 * ```
 */
interface MethodReflectionResolverInterface
{
    final public const SERVICE_TAG = 'silverstan.reflectionResolver.methodReflectionResolver';

    public function getConfigurationPropertyName(): string;

    /**
     * @return MethodReflection[]
     */
    public function resolve(ClassReflection $classReflection): array;
}
