<?php

declare(strict_types=1);

namespace Cambis\Silverstan\PhpDoc\TypeNodeResolverExtension;

use Cambis\Silverstan\Type\ObjectType\UnsafeObjectType;
use Override;
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
final readonly class DataObjectTypeNodeResolverExtension implements TypeNodeResolverExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    #[Override]
    public function resolve(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return null;
        }

        $className = $nameScope->resolveStringName($typeNode->name);

        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$classReflection->isSubclassOf('SilverStripe\ORM\DataObject')) {
            return null;
        }

        return new UnsafeObjectType($className, null, $classReflection);
    }
}
