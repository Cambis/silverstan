<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\ClassPropertiesNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Override;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

/**
 * @see \Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\ConfigurationPropertiesExtensionTest
 */
final class ConfigurationPropertiesExtension implements ReadWritePropertiesExtension
{
    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly PropertyReflectionAnalyser $propertyReflectionAnalyser
    ) {
    }

    #[Override]
    public function isAlwaysRead(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classReflectionAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection);
    }

    #[Override]
    public function isAlwaysWritten(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classReflectionAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection);
    }

    #[Override]
    public function isInitialized(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classReflectionAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyReflectionAnalyser->isConfigurationProperty($propertyReflection);
    }
}
