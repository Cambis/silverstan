<?php

declare(strict_types=1);

namespace Cambis\Silverstan\PhpDoc\TypeNodeResolverExtension;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeFactory\TypeFactory;
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
 * @see \Cambis\Silverstan\Tests\PhpDoc\TypeNodeResolverExtension\ExtensionOwnerTypeNodeResolverExtensionTest
 */
final class ExtensionOwnerTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    private TypeNodeResolver $typeNodeResolver;

    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, TypeFactory $typeFactory)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @throws ShouldNotHappenException
     */
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

            if ($classReflection->is('SilverStripe\Core\Extension')) {
                return true;
            }
        }

        return false;
    }
}
