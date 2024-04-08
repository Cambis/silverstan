<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\InClassNode;

use Cambis\Silverstan\Rule\InClassNode\RequireClassInAllowedNamespaceRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<RequireClassInAllowedNamespaceRule>
 */
final class RequireClassInAllowedNamespaceRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new RequireClassInAllowedNamespaceRule([
            [
                'class' => DataObject::class,
                'allowedNamespaces' => ['Model'],
            ],
        ]);
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/NotInAllowedNamespace.php', __DIR__ . '/Fixture/Model/InAllowedNamespace.php'], [
            [
                'Class Cambis\Silverstan\Tests\Rule\InClassNode\Fixture\NotInAllowedNamespace must be located in one of [Model] namespace.',
                8,
            ],
        ]);
    }
}
