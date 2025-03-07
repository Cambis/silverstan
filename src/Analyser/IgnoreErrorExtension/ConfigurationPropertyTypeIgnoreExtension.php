<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Analyser\IgnoreErrorExtension;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassPropertyNode;

/**
 * Ignore `missingType.iterableValue` value errors on configuration properties.
 */
final readonly class ConfigurationPropertyTypeIgnoreExtension implements IgnoreErrorExtension
{
    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private PropertyReflectionAnalyser $propertyReflectionAnalyser,
        private bool $enabled
    ) {
    }

    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if ($error->getIdentifier() !== 'missingType.iterableValue') {
            return false;
        }

        // @phpstan-ignore phpstanApi.instanceofAssumption
        if (!$node instanceof ClassPropertyNode) {
            return false;
        }

        if (!$this->classReflectionAnalyser->isConfigurable($node->getClassReflection())) {
            return false;
        }

        return $this->propertyReflectionAnalyser->isConfigurationProperty($node);
    }
}
