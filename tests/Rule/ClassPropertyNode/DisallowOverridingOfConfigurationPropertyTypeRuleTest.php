<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Cambis\Silverstan\Rule\ClassPropertyNode\DisallowOverridingOfConfigurationPropertyTypeRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<DisallowOverridingOfConfigurationPropertyTypeRule>
 */
final class DisallowOverridingOfConfigurationPropertyTypeRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowOverridingName.php'], [
            [
                'Type string|null of configuration property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\DisallowOverridingName::$table_name is not the same as type string of overridden configuration property ' . DataObject::class . '::$table_name.',
                12,
            ],
        ]);
    }

    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../tests.neon',
        ];
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowOverridingOfConfigurationPropertyTypeRule(
            self::getContainer()->getByType(ClassReflectionAnalyser::class),
            self::getContainer()->getByType(PropertyReflectionAnalyser::class),
            self::getContainer()->getByType(ReflectionResolver::class)
        );
    }
}
