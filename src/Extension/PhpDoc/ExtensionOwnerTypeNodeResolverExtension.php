<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\PhpDoc;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
use Override;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Allow the use of `SilverStripe\Core\Extensible&SilverStripe\Core\Extension` which would normally resolve to NEVER.
 *
 * @see \Cambis\Silverstan\Tests\Extension\PhpDoc\ExtensionOwnerTypeNodeResolverExtensionTest
 */
final class ExtensionOwnerTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{
    private TypeNodeResolver $typeNodeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly TypeFactory $typeFactory,
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

        $types = [];

        foreach ($typeNode->types as $node) {
            $type = $this->typeNodeResolver->resolve($node, $nameScope);

            if (!$this->isInternalTypeAcceptable($type)) {
                return null;
            }

            $types[] = $this->typeFactory->createExtensibleTypeFromType($type);
        }

        return TypeCombinator::intersect(...$types);
    }

    #[Override]
    public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver): void
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }

    private function isInternalTypeAcceptable(Type $type): bool
    {
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($this->classReflectionAnalyser->isExtensible($classReflection)) {
                return true;
            }

            if ($classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                return true;
            }
        }

        return false;
    }
}
