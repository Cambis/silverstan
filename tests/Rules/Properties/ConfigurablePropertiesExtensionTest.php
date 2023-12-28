<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rules\Properties;

use PHPStan\Rules\DeadCode\UnusedPrivatePropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UnusedPrivatePropertyRule>
 */
final class ConfigurablePropertiesExtensionTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(UnusedPrivatePropertyRule::class);
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testRuleConfigurableClass(): void
    {
        $this->analyse([__DIR__ . '/Fixture/configurable-class.php.inc'], [
            [
                'Property Cambis\Silverstan\Tests\Rules\Properties\Fixture\ConfigurableClass::$unconfigurable_property is never read, only written.',
                11,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
            [
                'Static property Cambis\Silverstan\Tests\Rules\Properties\Fixture\ConfigurableClass::$blocklisted_property is never read, only written.',
                16,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
        ]);
    }

    public function testRuleConfigurableExtension(): void
    {
        $this->analyse([__DIR__ . '/Fixture/configurable-extension.php.inc'], [
            [
                'Property Cambis\Silverstan\Tests\Rules\Properties\Fixture\ConfigurableExtension::$unconfigurable_property is never read, only written.',
                9,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
            [
                'Static property Cambis\Silverstan\Tests\Rules\Properties\Fixture\ConfigurableExtension::$blocklisted_property is never read, only written.',
                14,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
        ]);
    }
}
