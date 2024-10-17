<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeFactory;

use Cambis\Silverstan\Type\Concern\ExtensibleTypeTrait;
use Cambis\Silverstan\Type\UnsafeObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;

final class TypeFactory
{
    /**
     * Adds the `Cambis\Silverstan\Type\Concern\ExtensibleTypeTrait` to a type if it is a `PHPStan\Type\TypeWithClassName`.
     *
     * @see \Cambis\Silverstan\Type\Concern\ExtensibleTypeTrait
     */
    public function createExtensibleTypeFromType(Type $type): Type
    {
        /** @phpstan-ignore-next-line phpstanApi.instanceofType */
        if ($type instanceof ObjectType) {
            return new class($type->getClassName(), $type->getSubtractedType(), $type->getClassReflection()) extends ObjectType {
                use ExtensibleTypeTrait;
            };
        }

        if ($type instanceof ThisType) {
            return new class($type->getClassReflection(), $type->getSubtractedType()) extends ThisType {
                use ExtensibleTypeTrait;
            };
        }

        if ($type instanceof StaticType) {
            return new class($type->getClassReflection(), $type->getSubtractedType()) extends StaticType {
                use ExtensibleTypeTrait;
            };
        }

        return $type;
    }

    public function createUnsafeObjectTypeFromObjectType(ObjectType $type): UnsafeObjectType
    {
        return new UnsafeObjectType($type->getClassName(), $type->getSubtractedType(), $type->getClassReflection());
    }

    public function createObjectTypeFromUnsafeObjectType(UnsafeObjectType $type): ObjectType
    {
        return new ObjectType($type->getClassName(), $type->getSubtractedType(), $type->getClassReflection());
    }
}
