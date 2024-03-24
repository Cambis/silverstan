<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\Rule\ClassPropertyNode\DisallowUseOfReservedConfigurationPropertyNameRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<DisallowUseOfReservedConfigurationPropertyNameRule>
 */
final class DisallowUseOfReservedConfigurationPropertyNameRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowUseOfReservedConfigurationPropertyNameRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowReservedName.php'], [
            [
                'The name of non configuration property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\DisallowReservedName::$db is already used by the configuration property ' . DataObject::class . '::$db.',
                10,
                'Did you mean to declare the property as `private static` instead?',
            ],
        ]);
    }
}
