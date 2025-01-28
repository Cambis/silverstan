<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\Reflection\ExtensiblePropertyReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\PropertyReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;

final readonly class DBPropertyReflectionResolver implements PropertyReflectionResolverInterface
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private TypeResolver $typeResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'db';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $types = $this->typeResolver->resolveInjectedPropertyTypesFromConfigurationProperty($classReflection, 'db');

        $propertyReflections = [];

        foreach ($types as $name => $type) {
            $readableType = $this->getReadableType($classReflection, $type, $name);
            $writableType = $this->getWritableType($classReflection, $type, $name);

            $propertyReflections[$name] = new ExtensiblePropertyReflection($classReflection, $readableType, $writableType);
        }

        return $propertyReflections;
    }

    private function getReadableType(ClassReflection $classReflection, Type $type, string $name): Type
    {
        // Safety checks...
        if ($type->isObject()->no()) {
            return $type;
        }

        if ($type->getObjectClassReflections() === []) {
            return $type;
        }

        $fieldClassReflection = $type->getObjectClassReflections()[0];

        if (!$fieldClassReflection->isSubclassOf('SilverStripe\ORM\FieldType\DBField')) {
            return $type;
        }

        // Check for custom `get<name>` function https://docs.silverstripe.org/en/5/developer_guides/model/data_types_and_casting/#overriding
        if ($classReflection->hasNativeMethod('get' . $name)) {
            return $classReflection->getNativeMethod('get' . $name)->getVariants()[0]->getReturnType();
        }

        // Attempt to return the type from the property
        if ($fieldClassReflection->hasProperty('value')) {
            return $fieldClassReflection->getProperty('value', new OutOfClassScope())->getReadableType();
        }

        // Fallback, return the original type
        return $type;
    }

    private function getWritableType(ClassReflection $classReflection, Type $type, string $name): Type
    {
        // Safety checks...
        if ($type->isObject()->no()) {
            return $type;
        }

        if ($type->getObjectClassReflections() === []) {
            return $type;
        }

        $fieldClassReflection = $type->getObjectClassReflections()[0];

        if (!$fieldClassReflection->isSubclassOf('SilverStripe\ORM\FieldType\DBField')) {
            return $type;
        }

        // Check for custom `set<name>` function https://docs.silverstripe.org/en/5/developer_guides/model/data_types_and_casting/#overriding
        if ($classReflection->hasNativeMethod('set' . $name)) {
            $parameters = $classReflection->getNativeMethod('set' . $name)->getVariants()[0]->getParameters();

            if ($parameters !== []) {
                return $parameters[0]->getType();
            }
        }

        // Attempt to return the type from the property
        if ($fieldClassReflection->hasProperty('value')) {
            return $fieldClassReflection->getProperty('value', new OutOfClassScope())->getWritableType();
        }

        // Fallback, return the original type
        return $type;
    }
}
