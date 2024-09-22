<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeFactory;

use Cambis\Silverstan\Type\Concern\ExtensibleTypeTrait;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;

final class TypeFactory
{
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
}
