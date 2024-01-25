<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\ClassPropertiesNode;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function str_contains;

/**
 * @see \Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ConfigurablePropertiesExtensionTest
 */
final class ConfigurablePropertiesExtension implements ReadWritePropertiesExtension, DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Allows configurable read-write properties.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private array $bar = [];
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class Foo extends \SilverStripe\ORM\DataObject
{
    private static array $bar = [];
}
CODE_SAMPLE
            ),
            ]
        );
    }

    public function isAlwaysRead(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return !$this->shouldSkipProperty($propertyReflection);
    }

    public function isAlwaysWritten(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return !$this->shouldSkipProperty($propertyReflection);
    }

    public function isInitialized(PropertyReflection $propertyReflection, string $propertyName): bool
    {
        return !$this->shouldSkipProperty($propertyReflection);
    }

    private function shouldSkipClass(ClassReflection $classReflection): bool
    {
        if ($classReflection->isSubclassOf(Extension::class)) {
            return false;
        }

        return !$classReflection->hasTraitUse(Configurable::class);
    }

    private function shouldSkipProperty(PropertyReflection $propertyReflection): bool
    {
        $classReflection = $propertyReflection->getDeclaringClass();

        if ($this->shouldSkipClass($classReflection)) {
            return true;
        }

        if (!$propertyReflection->isPrivate()) {
            return true;
        }

        if (!$propertyReflection->isStatic()) {
            return true;
        }

        return str_contains((string) $propertyReflection->getDocComment(), '@internal');
    }
}
