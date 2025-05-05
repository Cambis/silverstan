<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\ClassPropertiesNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Override;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

/**
 * This extension marks configuration properties as always read and written.
 *
 * @see \Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\ConfigurationPropertiesExtensionTest
 * @see https://phpstan.org/developing-extensions/always-read-written-properties
 */
final class ConfigurationPropertiesExtension implements ReadWritePropertiesExtension
{
    /**
     * @readonly
     */
    private ClassReflectionAnalyser $classReflectionAnalyser;
    /**
     * @readonly
     */
    private PropertyReflectionAnalyser $propertyReflectionAnalyser;
    public function __construct(ClassReflectionAnalyser $classReflectionAnalyser, PropertyReflectionAnalyser $propertyReflectionAnalyser)
    {
        $this->classReflectionAnalyser = $classReflectionAnalyser;
        $this->propertyReflectionAnalyser = $propertyReflectionAnalyser;
    }

    public function isAlwaysRead(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classReflectionAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection);
    }

    public function isAlwaysWritten(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classReflectionAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection);
    }

    public function isInitialized(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classReflectionAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection);
    }
}
