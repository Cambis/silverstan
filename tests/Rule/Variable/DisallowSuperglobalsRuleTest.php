<?php

namespace Cambis\Silverstan\Tests\Rule\Variable;

use Cambis\Silverstan\Rule\Variable\DisallowSuperglobalsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowSuperglobalsRule>
 */
final class DisallowSuperglobalsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DisallowSuperglobalsRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/disallow-superglobals.php.inc'], [
            [
                'You should not directly access the $_GET superglobal. Consider using an alternative.',
                9,
            ],
        ]);
    }
}
