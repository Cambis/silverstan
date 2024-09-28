<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;

/**
 * This service is used to resolve the injected method types from a configuration property.
 *
 * ```
 * services:
 * 	-
 *		class: App\PHPStan\TypeResolver\MyMethodTypeResolver
 *		tags:
 *			- silverstan.typeResolver.methodTypeResolver
 * ```
 */
interface MethodTypeResolverInterface
{
    public function getConfigurationPropertyName(): string;

    /**
     * @return Type[]
     */
    public function resolve(ClassReflection $classReflection): array;
}
