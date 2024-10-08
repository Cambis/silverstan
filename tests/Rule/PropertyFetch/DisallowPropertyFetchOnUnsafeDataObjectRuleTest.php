<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\PropertyFetch;

use Cambis\Silverstan\Rule\PropertyFetch\DisallowPropertyFetchOnUnsafeDataObjectRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowPropertyFetchOnUnsafeDataObjectRule>
 */
final class DisallowPropertyFetchOnUnsafeDataObjectRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowPropertyFetchDataObject.php'], [
            [
                'Accessing $foo->Bar()->Title is potentially unsafe, as $foo->Bar() may not exist in the database. Call $foo->Bar()->exists() first to verify that it is safe to access.',
                9,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
            ],
            [
                'Accessing $foo->Bar()->Title is potentially unsafe, as $foo->Bar() may not exist in the database. Call $foo->Bar()->exists() first to verify that it is safe to access.',
                17,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
            ],
            [
                'Accessing $foo->Bar()->Title is potentially unsafe, as $foo->Bar() may not exist in the database. Call $foo->Bar()->exists() first to verify that it is safe to access.',
                24,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
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
        return new DisallowPropertyFetchOnUnsafeDataObjectRule();
    }
}
