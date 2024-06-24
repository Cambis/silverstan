<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\CollectedDataNode;

use Cambis\Silverstan\Collector\Expr\MagicDataObjectCallCollector;
use Cambis\Silverstan\Rule\CollectedDataNode\DisallowUnsafeAccessOfMagicDataObjectRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowUnsafeAccessOfMagicDataObjectRule>
 */
final class DisallowUnsafeAccessOfMagicDataObjectRuleTest extends RuleTestCase
{
    public function testRuleMethods(): void
    {
        $this->analyse([__DIR__ . '/Fixture/UnsafeMethodAccess.php'], [
            [
                'Call exists() first before accessing any magic \SilverStripe\ORM\DataObject methods or properties.',
                15,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
            ],
        ]);
    }

    public function testRuleProperties(): void
    {
        $this->analyse([__DIR__ . '/Fixture/UnsafePropertyAccess.php'], [
            [
                'Call exists() first before accessing any magic \SilverStripe\ORM\DataObject methods or properties.',
                17,
                'See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists',
            ],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowUnsafeAccessOfMagicDataObjectRule();
    }

    #[Override]
    protected function getCollectors(): array
    {
        return [
            new MagicDataObjectCallCollector(),
        ];
    }
}
