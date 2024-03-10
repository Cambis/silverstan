<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\Rule\ClassPropertyNode\DisallowUseOfReservedConfigurablePropertyNameRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowUseOfReservedConfigurablePropertyNameRule>
 */
final class DisallowUseOfReservedConfigurablePropertyNameRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DisallowUseOfReservedConfigurablePropertyNameRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/disallow-reserved-name.php.inc'], [
            [
                'The name of non configurable property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\DisallowReservedName::$db is already used by the configurable property SilverStripe\ORM\DataObject::$db.',
                07,
                'Did you mean to declare the property as `private static` instead?',
            ],
        ]);
    }
}
