<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\StaticPropertyFetch;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionAnalyser\PropertyReflectionAnalyser;
use Cambis\Silverstan\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurationPropertyRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowStaticPropertyFetchOnConfigurationPropertyRule>
 */
final class DisallowStaticPropertyFetchOnConfigurationPropertyRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowStaticPropertyFetch.php'], [
            [
                'Unsafe access to configuration property Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\Fixture\DisallowStaticPropertyFetch::$foo through self::. Use self::config->get(\'foo\') instead.',
                23,
                'See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties',
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
        return new DisallowStaticPropertyFetchOnConfigurationPropertyRule(
            self::getContainer()->getByType(ClassReflectionAnalyser::class),
            self::getContainer()->getByType(PropertyReflectionAnalyser::class)
        );
    }
}
