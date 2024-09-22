<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\PhpDoc;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Override;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;

/**
 * Allow the use of `Extensible&Extension` which would normally resolve to NEVER.
 *
 * @see \Cambis\Silverstan\Tests\Extension\PhpDoc\ExtensionOwnerTypeNodeResolverExtensionTest
 */
final readonly class ExtensionOwnerTypeNodeResolverExtension implements TypeNodeResolverExtension
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private TypeNodeResolver $typeNodeResolver
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

            $types[] = $type;
        }

        return new IntersectionType($types);
    }

    private function isInternalTypeAcceptable(Type $type): bool
    {
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($this->classAnalyser->isExtensible($classReflection)) {
                return true;
            }

            if ($classReflection->isSubclassOf('SilverStripe\Core\Extension')) {
                return true;
            }
        }

        return false;
    }
}
