<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\NodeAnalyser\ClassAnalyser;
use Cambis\Silverstan\NodeAnalyser\PropertyAnalyser;
use Cambis\Silverstan\Rule\ClassPropertyNode\RequireConfigurationPropertySnakeCaseNameRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RequireConfigurationPropertySnakeCaseNameRule>
 */
final class RequireConfigurationPropertySnakeCaseNameRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/RequireSnakeCase.php'], [
            [
                'Configuration property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\RequireSnakeCase::$fooBar must be in snake_case format.',
                10,
            ],
        ]);
    }

    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../../extension.neon',
        ];
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new RequireConfigurationPropertySnakeCaseNameRule(
            self::getContainer()->getByType(ClassAnalyser::class),
            self::getContainer()->getByType(PropertyAnalyser::class)
        );
    }
}
