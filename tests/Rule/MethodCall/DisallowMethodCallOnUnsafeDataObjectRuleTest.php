<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\MethodCall;

use Cambis\Silverstan\Rule\MethodCall\DisallowMethodCallOnUnsafeDataObjectRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowMethodCallOnUnsafeDataObjectRule>
 */
final class DisallowMethodCallOnUnsafeDataObjectRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowMethodCallDataObject.php'], [
            [
                'Accessing $foo->Bar()->doSomethingPotentiallyDangerous() is potentially unsafe, as $foo->Bar() may not exist in the database. Call $foo->Bar()->exists() first to verify that it is safe to access.',
                9,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
            ],
            [
                'Accessing $foo->Bar()->doSomethingPotentiallyDangerous() is potentially unsafe, as $foo->Bar() may not exist in the database. Call $foo->Bar()->exists() first to verify that it is safe to access.',
                16,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
            ],
            [
                'Accessing $foo->Bar()->doSomethingPotentiallyDangerous() is potentially unsafe, as $foo->Bar() may not exist in the database. Call $foo->Bar()->exists() first to verify that it is safe to access.',
                23,
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
        return new DisallowMethodCallOnUnsafeDataObjectRule(['doSomethingSafe']);
    }
}
