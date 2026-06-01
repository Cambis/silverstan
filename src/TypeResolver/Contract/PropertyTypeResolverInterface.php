<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;

/**
 * This service is used to resolve the injected property types from a configuration property.
 *
 * ```
 * services:
 * 	-
 *		class: App\PHPStan\TypeResolver\MyPropertyTypeResolver
 *		tags:
 *			- silverstan.typeResolver.propertyTypeResolver
 * ```
 */
interface PropertyTypeResolverInterface
{
    public const SERVICE_NAME = 'silverstan.typeResolver.propertyTypeResolver';

    public function getConfigurationPropertyName(): string;

    /**
     * @return int|true
     */
    public function getExcludeMiddleware();

    /**
     * @return array<non-empty-string, Type>
     */
    public function resolve(ClassReflection $classReflection): array;
}
