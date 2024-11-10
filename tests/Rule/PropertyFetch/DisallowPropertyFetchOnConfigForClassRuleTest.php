<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\PropertyFetch;

use Cambis\Silverstan\Rule\PropertyFetch\DisallowPropertyFetchOnConfigForClassRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowPropertyFetchOnConfigForClassRule>
 */
final class DisallowPropertyFetchOnConfigForClassRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowPropertyFetch.php'], [
            [
                'Cannot resolve the type of Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture\DisallowPropertyFetch::config()->foo. Use Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture\DisallowPropertyFetch::config()->get(\'foo\') instead.',
                16,
            ],
            [
                'Cannot resolve the type of Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture\DisallowPropertyFetch::config()->foo. Use Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture\DisallowPropertyFetch::config()->get(\'foo\') instead.',
                19,
            ],
            [
                'Cannot resolve the type of Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture\DisallowPropertyFetch::config()->foo. Use Cambis\Silverstan\Tests\Rule\PropertyFetch\Fixture\DisallowPropertyFetch::config()->get(\'foo\') instead.',
                22,
            ],
        ]);
    }

    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../tests.neon',
        ];
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowPropertyFetchOnConfigForClassRule();
    }
}
