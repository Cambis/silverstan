<?php

namespace Cambis\Silverstan\Tests\Rule\Variable;

use Cambis\Silverstan\Rule\Variable\ForbidSuperglobalsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidSuperglobalsRule>
 */
final class ForbidSuperglobalsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidSuperglobalsRule();
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
