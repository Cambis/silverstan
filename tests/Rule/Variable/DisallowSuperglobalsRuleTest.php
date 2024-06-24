<?php

namespace Cambis\Silverstan\Tests\Rule\Variable;

use Cambis\Silverstan\Rule\Variable\DisallowSuperglobalsRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowSuperglobalsRule>
 */
final class DisallowSuperglobalsRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowSuperglobals.php'], [
            [
                'You should not directly access the $_GET superglobal. Consider using an alternative.',
                12,
            ],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowSuperglobalsRule();
    }
}
