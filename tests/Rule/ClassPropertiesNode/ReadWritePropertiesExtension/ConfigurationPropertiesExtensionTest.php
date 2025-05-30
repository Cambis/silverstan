<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ReadWritePropertiesExtension;

use Override;
use PHPStan\Rules\DeadCode\UnusedPrivatePropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UnusedPrivatePropertyRule>
 */
final class ConfigurationPropertiesExtensionTest extends RuleTestCase
{
    /**
     * @return string[]
     */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../tests.neon'];
    }

    public function testRuleConfigurableClass(): void
    {
        $this->analyse([__DIR__ . '/Fixture/ConfigurableClass.php'], [
            [
                'Property Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ReadWritePropertiesExtension\Fixture\ConfigurableClass::$unconfigurable_property is never read, only written.',
                14,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
            [
                'Static property Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ReadWritePropertiesExtension\Fixture\ConfigurableClass::$blocklisted_property is never read, only written.',
                19,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
        ]);
    }

    public function testRuleConfigurableExtension(): void
    {
        $this->analyse([__DIR__ . '/Fixture/ConfigurableExtension.php'], [
            [
                'Property Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ReadWritePropertiesExtension\Fixture\ConfigurableExtension::$unconfigurable_property is never read, only written.',
                12,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
            [
                'Static property Cambis\Silverstan\Tests\Rule\ClassPropertiesNode\ReadWritePropertiesExtension\Fixture\ConfigurableExtension::$blocklisted_property is never read, only written.',
                17,
                'See: https://phpstan.org/developing-extensions/always-read-written-properties',
            ],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        /** @phpstan-ignore-next-line phpstanApi.classConstant */
        return self::getContainer()->getByType(UnusedPrivatePropertyRule::class);
    }
}
