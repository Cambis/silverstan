<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\Interface_;

use Cambis\Silverstan\Rule\Interface_\RequireInterfaceInAllowedNamespaceRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RequireInterfaceInAllowedNamespaceRule>
 */
final class RequireInterfaceInAllowedNamespaceRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Fixture/NotInAllowedNamespaceInterface.php',
                __DIR__ . '/Fixture/Contract/InAllowedNamespaceInterface.php',
            ],
            [
                [
                    'Interface Cambis\Silverstan\Tests\Rule\Interface_\Fixture\NotInAllowedNamespaceInterface must be located in one of [Contract] namespace.',
                    7,
                    null,
                ],
            ]
        );
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new RequireInterfaceInAllowedNamespaceRule(
            ['Contract']
        );
    }
}
