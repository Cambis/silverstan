<?php

namespace Cambis\Silverstan\Tests\Rules\Variable;

use Cambis\Silverstan\Rules\Variable\ForbidSuperglobalsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidSuperglobalsRule>
 */
final class ForbidSuperglobalsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidSuperglobalsRule(
            ForbidSuperglobalsRule::SUPERGLOBALS,
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/forbid-superglobals.php.inc'], [
            [
                'You should not directly access the $_GET superglobal. Consider using an alternative.',
                9,
            ],
        ]);
    }
}
