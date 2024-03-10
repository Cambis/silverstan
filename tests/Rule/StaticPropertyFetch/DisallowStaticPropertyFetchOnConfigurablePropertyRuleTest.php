<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\StaticPropertyFetch;

use Cambis\Silverstan\Rule\StaticPropertyFetch\DisallowStaticPropertyFetchOnConfigurablePropertyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowStaticPropertyFetchOnConfigurablePropertyRule>
 */
final class DisallowStaticPropertyFetchOnConfigurablePropertyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DisallowStaticPropertyFetchOnConfigurablePropertyRule();
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/disallow-static-property-fetch.php.inc'], [
            [
                'Unsafe access to configurable property Cambis\Silverstan\Tests\Rule\StaticPropertyFetch\Fixture\DisallowStaticPropertyFetch::$foo through self::.',
                20,
                'See: https://docs.silverstripe.org/en/5/developer_guides/configuration/configuration/#accessing-configuration-properties',
            ],
        ]);
    }
}
