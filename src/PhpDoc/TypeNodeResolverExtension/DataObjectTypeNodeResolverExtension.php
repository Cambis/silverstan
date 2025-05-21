<?php

declare(strict_types=1);

namespace Cambis\Silverstan\PhpDoc\TypeNodeResolverExtension;

use Cambis\Silverstan\Type\ObjectType\UnsafeObjectType;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

/**
 * Marks any magic `SilverStripe\ORM\DataObject` as 'unsafe', i.e. the record may not exist in the database.
 *
 * @see \Cambis\Silverstan\Tests\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRuleTest
 */
final class DataObjectTypeNodeResolverExtension implements TypeNodeResolverExtension
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return null;
        }
        $className = $nameScope->resolveStringName($typeNode->name);
        if (!$this->reflectionProvider->hasClass('SilverStripe\ORM\DataObject')) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->isSubclassOfClass($this->reflectionProvider->getClass('SilverStripe\ORM\DataObject'))) {
            return null;
        }
        return new UnsafeObjectType($className, null, $classReflection);
    }
}
