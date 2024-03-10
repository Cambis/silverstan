<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassMethod;

use Cambis\Silverstan\Rule\ClassMethod\DisallowNonInterfacePublicMethodsOnExtensionRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowNonInterfacePublicMethodsOnExtensionRule>
 */
final class DisallowNonInterfacePublicMethodsOnExtensionRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowNonInterfacePublicMethodsOnExtensionRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/disallowed-extension.php.inc'], [
            [
                'Use explicit interface contract or a service to define public facing extension methods.',
                12,
            ],
        ]);
    }
}
