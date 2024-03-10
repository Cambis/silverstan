<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\Rule\ClassPropertyNode\DisallowOverridingOfConfigurablePropertyTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowOverridingOfConfigurablePropertyTypeRule>
 */
final class DisallowOverridingOfConfigurablePropertyTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DisallowOverridingOfConfigurablePropertyTypeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/disallow-overriding-type.php.inc'], [
            [
                'Type string|null of configurable property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\DisallowOverridingName::$table_name is not the same as type string of overridden configurable property SilverStripe\ORM\DataObject::$table_name.',
                9,
            ],
        ]);
    }
}
