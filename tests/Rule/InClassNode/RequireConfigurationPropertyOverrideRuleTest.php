<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\InClassNode;

use Cambis\Silverstan\Normaliser\Normaliser;
use Cambis\Silverstan\Rule\InClassNode\RequireConfigurationPropertyOverrideRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<RequireConfigurationPropertyOverrideRule>
 */
final class RequireConfigurationPropertyOverrideRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/RequireTableName.php'], [
            [
                'Class Cambis\Silverstan\Tests\Rule\InClassNode\Fixture\RequireTableName is missing required configuration property $table_name',
                8,
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
        return new RequireConfigurationPropertyOverrideRule(
            self::getContainer()->getByType(Normaliser::class),
            [
                DataObject::class => ['table_name'],
            ],
        );
    }
}
