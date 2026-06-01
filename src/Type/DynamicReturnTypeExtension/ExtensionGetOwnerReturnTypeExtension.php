<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\DynamicReturnTypeExtension;

use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use function array_key_exists;
use function in_array;

/**
 * This extension resolves the return type of `SilverStripe\Core\Extension::getOwner()` if the class is missing the `@extends` annotation.
 *
 * @see \Cambis\Silverstan\Tests\Type\DynamicReturnTypeExtension\ExtensionGetOwnerReturnTypeExtensionTest
 */
final class ExtensionGetOwnerReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @readonly
     */
    private TypeResolver $typeResolver;
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'getOwner',
    ];

    public function __construct(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    #[Override]
    public function getClass(): string
    {
        return 'SilverStripe\Core\Extension';
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    #[Override]
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $extensionType = $scope->getType($methodCall->var);

        if ($extensionType->getObjectClassReflections() === []) {
            return null;
        }

        $classReflection = $extensionType->getObjectClassReflections()[0];

        // Extension already has an `@extends` annotation, use the type from that
        if ($classReflection->getExtendsTags() !== []) {
            return null;
        }

        $types = $this->typeResolver->resolveInjectedPropertyTypesFromConfigurationProperty($classReflection, '__silverstan_owners');

        // Safety checks...
        if ($types === []) {
            return null;
        }

        if (!array_key_exists('__getOwners', $types)) {
            return null;
        }

        $type = $types['__getOwners'];

        /** @phpstan-ignore phpstanApi.instanceofType */
        if (!$type instanceof GenericObjectType) {
            return null;
        }

        if ($type->getTypes() === []) {
            return null;
        }

        return $type->getTypes()[0];
    }
}
