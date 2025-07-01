<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\Contract;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
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
    public const SERVICE_NAME = 'silverstan.typeResolver.methodTypeResolver';

    public function getConfigurationPropertyName(): string;

    /**
     * @return true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
     */
    public function getExcludeMiddleware();

    /**
     * @return array<non-empty-string, Type>
     */
    public function resolve(ClassReflection $classReflection): array;
}
