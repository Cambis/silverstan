<?php

namespace Cambis\Silverstan\Tests\Rule\StaticPropertyFetch;

use Cambis\Silverstan\Rule\StaticPropertyFetch\ForbidStaticPropertyFetchOnConfigurablePropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidStaticPropertyFetchOnConfigurablePropertyRule>
 */
final class ForbidStaticPropertyFetchOnConfigurablePropertyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ForbidStaticPropertyFetchOnConfigurablePropertyRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/forbid-static-property-fetch.php.inc'], [
            [
                'Unsafe access to configurable property Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\Fixture\ForbidStaticPropertyFetch::$foo through self::.',
                20,
                'See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties',
            ],
        ]);
    }
}
