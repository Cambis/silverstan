<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Type\DynamicReturnTypeExtension;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use function array_key_exists;
use function in_array;
use function is_array;
use function strtok;

/**
 * This extension resolves the return type of `SilverStripe\ORM\DataObject::dbObject()`.
 *
 * @see \Cambis\Silverstan\Tests\Type\DynamicMethodReturnTypeExtension\DataObjectDbObjectReturnTypeExtensionTest
 */
final readonly class DataObjectDbObjectReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'dbObject',
    ];

    public function __construct(
        private ConfigurationResolver $configurationResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    #[Override]
    public function getClass(): string
    {
        return 'SilverStripe\ORM\DataObject';
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), self::SUPPORTED_METHODS, true);
    }

    #[Override]
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        // Type to represent the $fieldName argument
        $fieldNameType = $scope->getType($methodCall->getArgs()[0]->value);

        // Field name must be a string
        if ($fieldNameType->isString()->no()) {
            return null;
        }

        if ($fieldNameType->getConstantStrings() === []) {
            return null;
        }

        $fieldName = $fieldNameType->getConstantStrings()[0]->getValue();

        // Type to represent the holder, i.e. the left side of the dbObject() call
        $objectType = $scope->getType($methodCall->var);

        if ($objectType->isObject()->no()) {
            return null;
        }

        if ($objectType->getObjectClassReflections() === []) {
            return null;
        }

        $db = $this->configurationResolver->get($objectType->getObjectClassReflections()[0]->getName(), 'db');

        if (!is_array($db) || $db === []) {
            return null;
        }

        /** @var string[] $db */
        if (!array_key_exists($fieldName, $db)) {
            return null;
        }

        $fieldClassName = $db[$fieldName];

        // Remove trailing brackets e.g. Varchar(255) -> Varchar
        $fieldClassName = strtok($fieldClassName, '(');

        if ($fieldClassName === false) {
            return null;
        }

        $fieldClassName = $this->configurationResolver->resolveClassName($fieldClassName);

        if (!$this->reflectionProvider->hasClass($fieldClassName)) {
            return null;
        }

        return new ObjectType($fieldClassName);
    }
}
