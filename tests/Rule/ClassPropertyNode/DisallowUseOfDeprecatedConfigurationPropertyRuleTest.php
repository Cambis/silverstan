<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\ReflectionResolver;
use Cambis\Silverstan\Rule\ClassPropertyNode\DisallowUsageOfDeprecatedConfigurationPropertyRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowUsageOfDeprecatedConfigurationPropertyRule>
 */
final class DisallowUseOfDeprecatedConfigurationPropertyRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/Bar.php'], [
            [
                'Access to deprecated configuration property $deprecated_property of class Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\Foo.',
                13,
            ],
            [
                "Access to deprecated configuration property \$deprecated_property_with_message of class Cambis\\Silverstan\\Tests\\Rule\\ClassPropertyNode\\Fixture\\Foo:\nreason.",
                15,
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
        return new DisallowUsageOfDeprecatedConfigurationPropertyRule(
            self::getContainer()->getByType(ClassReflectionAnalyser::class),
            self::getContainer()->getByType(PropertyReflectionAnalyser::class),
            self::getContainer()->getByType(ReflectionResolver::class)
        );
    }
}
