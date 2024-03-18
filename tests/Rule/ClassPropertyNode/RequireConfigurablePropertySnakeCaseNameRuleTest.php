<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\Rule\ClassPropertyNode\RequireConfigurablePropertySnakeCaseNameRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RequireConfigurablePropertySnakeCaseNameRule>
 */
final class RequireConfigurablePropertySnakeCaseNameRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new RequireConfigurablePropertySnakeCaseNameRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/RequireSnakeCase.php'], [
            [
                'Configurable property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\RequireSnakeCase::$fooBar must be in snake_case format.',
                10,
            ],
        ]);
    }
}
