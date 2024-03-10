<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\InClassNode;

use Cambis\Silverstan\Rule\InClassNode\RequireConfigurablePropertyOverrideRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<RequireConfigurablePropertyOverrideRule>
 */
final class RequireConfigurablePropertyOverrideRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RequireConfigurablePropertyOverrideRule([
            [
                'class' => DataObject::class,
                'properties' => ['table_name'],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/require-table-name.php.inc'], [
            [
                'Class Cambis\Silverstan\Tests\Rule\InClassNode\Fixture\RequireTableName is missing configurable property $table_name',
                5,
            ],
        ]);
    }
}
