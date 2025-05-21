<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
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
     * @return true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
     */
    public function getExcludeMiddleware();

    /**
     * @return Type[]
     */
    public function resolve(ClassReflection $classReflection): array;
}
