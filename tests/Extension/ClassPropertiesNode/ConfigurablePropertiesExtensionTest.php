<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Extension\ClassPropertiesNode;

use Override;
use PHPStan\Rules\DeadCode\UnusedPrivatePropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UnusedPrivatePropertyRule>
 */
final class ConfigurablePropertiesExtensionTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(UnusedPrivatePropertyRule::class);
    }

    /**
     * @return string[]
     */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testRuleConfigurableClass(): void
    {
        $this->analyse([__DIR__ . '/Fixture/ConfigurableClass.php'], [
            [
                'Property Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\Fixture\ConfigurableClass::$unconfigurable_property is never read, only written.',
                14,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
            [
                'Static property Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\Fixture\ConfigurableClass::$blocklisted_property is never read, only written.',
                19,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
        ]);
    }

    public function testRuleConfigurableExtension(): void
    {
        $this->analyse([__DIR__ . '/Fixture/ConfigurableExtension.php'], [
            [
                'Property Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\Fixture\ConfigurableExtension::$unconfigurable_property is never read, only written.',
                12,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
            [
                'Static property Cambis\Silverstan\Tests\Extension\ClassPropertiesNode\Fixture\ConfigurableExtension::$blocklisted_property is never read, only written.',
                17,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
        ]);
    }
}
