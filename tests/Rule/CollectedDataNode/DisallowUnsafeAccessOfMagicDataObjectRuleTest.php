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

    public function testRuleMethods(): void
    {
        $this->analyse([__DIR__ . '/Fixture/UnsafeMethodAccess.php'], [
            [
                'Call exists() first before accessing any \SilverStripe\ORM\DataObject methods or properties.',
                15,
            ],
        ]);
    }

    public function testRuleProperties(): void
    {
        $this->analyse([__DIR__ . '/Fixture/UnsafePropertyAccess.php'], [
            [
                'Call exists() first before accessing any \SilverStripe\ORM\DataObject methods or properties.',
                17,
            ],
        ]);
    }
}
