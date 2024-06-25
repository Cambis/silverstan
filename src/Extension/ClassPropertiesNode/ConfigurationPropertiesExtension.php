<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Extension\ClassPropertiesNode;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use Override;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

/**
 * @see \Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\ConfigurationPropertiesExtensionTest
 */
final readonly class ConfigurationPropertiesExtension implements ReadWritePropertiesExtension
{
    public function __construct(
        private ClassAnalyser $classAnalyser,
        private PropertyAnalyser $propertyAnalyser
    ) {
    }

    #[Override]
    public function isAlwaysRead(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyAnalyser->isConfigurationProperty($propertyReflection);
    }

    #[Override]
    public function isAlwaysWritten(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyAnalyser->isConfigurationProperty($propertyReflection);
    }

    #[Override]
    public function isInitialized(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return $this->classAnalyser->isConfigurable($propertyReflection->getDeclaringClass()) &&
            $this->propertyAnalyser->isConfigurationProperty($propertyReflection);
    }
}
