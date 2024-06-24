<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\Trait_;

use Cambis\Silverstan\Rule\Trait_\RequireTraitInAllowedNamespaceRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RequireTraitInAllowedNamespaceRule>
 */
final class RequireTraitInAllowedNamespaceRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse(
            [
                __DIR__ . '/Fixture/NotInAllowedNamespaceTrait.php',
                __DIR__ . '/Fixture/Concern/InAllowedNamespaceTrait.php',
            ],
            [
                [
                    'Trait Cambis\Silverstan\Tests\Rule\Trait_\Fixture\NotInAllowedNamespaceTrait must be located in one of [Concern] namespace.',
                    5,
                    null,
                ],
            ]
        );
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new RequireTraitInAllowedNamespaceRule(
            ['Concern']
        );
    }
}
