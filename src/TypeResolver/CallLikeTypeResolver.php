<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver;

use PhpParser\Node\Arg;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;

final class CallLikeTypeResolver
{
    /**
     * @param Arg[] $args
     * @return Type[]
     */
    public function resolveTypesFromArgs(array $args, Scope $scope): array
    {
        $types = [];

        foreach ($args as $arg) {
            $types[] = $scope->getType($arg->value);
        }

        return $types;
    }

    /**
     * @param ParameterReflection[] $parameters
     * @return Type[]
     */
    public function resolveTypesFromParameters(array $parameters): array
    {
        $types = [];

        foreach ($parameters as $parameter) {
            $types[] = $parameter->getType();
        }

        return $types;
    }
}
