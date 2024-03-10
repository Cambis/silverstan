<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\PhpDoc;

use Override;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Extension;

use function count;

/**
 * Allow the use of `Extensible&Extension` which would normally resolve to NEVER.
 *
 * @see \Cambis\Silverstan\Tests\Extension\PhpDoc\ExtensionOwnerTypeNodeResolverExtensionTest
 */
final class ExtensionOwnerTypeNodeResolverExtension implements TypeNodeResolverExtension
{
    public function __construct(
        private readonly TypeNodeResolver $typeNodeResolver
    ) {
    }

    /**
     * @throws ShouldNotHappenException
     */
    #[Override]
    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if (!$typeNode instanceof IntersectionTypeNode) {
            return null;
        }

        // Limit the amount of types, there should be only two
        if (count($typeNode->types) !== 2) {
            return null;
        }

        $extensibleType = $this->typeNodeResolver->resolve($typeNode->types[0], $nameScope);
        $extensionType = $this->typeNodeResolver->resolve($typeNode->types[1], $nameScope);

        if ($this->shouldSkipExtensibleType($extensibleType)) {
            return null;
        }

        if ($this->shouldSkipExtensionType($extensionType)) {
            return null;
        }

        return new IntersectionType([$extensibleType, $extensionType]);
    }

    private function shouldSkipExtensibleType(Type $type): bool
    {
        if (!$type instanceof ObjectType) {
            return true;
        }

        $classReflection = $type->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return true;
        }

        return !$classReflection->hasTraitUse(Extensible::class);
    }

    private function shouldSkipExtensionType(Type $type): bool
    {
        if (!$type instanceof StaticType && !$type instanceof ObjectType) {
            return true;
        }

        $classReflection = $type->getClassReflection();

        if (!$classReflection instanceof ClassReflection) {
            return true;
        }

        return !$classReflection->isSubclassOf(Extension::class);
    }
}
