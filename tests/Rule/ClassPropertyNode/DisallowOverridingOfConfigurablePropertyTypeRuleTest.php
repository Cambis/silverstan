<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassPropertyNode;

use Cambis\Silverstan\Rule\ClassPropertyNode\DisallowOverridingOfConfigurablePropertyTypeRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<DisallowOverridingOfConfigurablePropertyTypeRule>
 */
final class DisallowOverridingOfConfigurablePropertyTypeRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowOverridingOfConfigurablePropertyTypeRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowOverridingName.php'], [
            [
                'Type string|null of configurable property Cambis\Silverstan\Tests\Rule\ClassPropertyNode\Fixture\DisallowOverridingName::$table_name is not the same as type string of overridden configurable property ' . DataObject::class . '::$table_name.',
                12,
            ],
        ]);
    }
}
