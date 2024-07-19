<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\ClassMethod;

use Cambis\Silverstan\Rule\ClassMethod\RequireParentCallInOverridenMethodRule;
use Override;
use PhpParser\NodeFinder;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SilverStripe\ORM\DataObject;

/**
 * @extends RuleTestCase<RequireParentCallInOverridenMethodRule>
 */
final class RequireParentCallInOverridenMethodRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DataObjectWrite.php'], [
            [
                'Class method Cambis\Silverstan\Tests\Rule\ClassMethod\Fixture\DataObjectWrite::requireDefaultRecords() should call parent::requireDefaultRecords() first.',
                10,
            ],
            [
                'Class method Cambis\Silverstan\Tests\Rule\ClassMethod\Fixture\DataObjectWrite::onBeforeWrite() is missing required call to parent::onBeforeWrite().',
                23,
            ],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new RequireParentCallInOverridenMethodRule(
            new NodeFinder(),
            [
                [
                    'class' => DataObject::class,
                    'method' => 'onBeforeWrite',
                ],
                [
                    'class' => DataObject::class,
                    'method' => 'onAfterWrite',
                    'isFirst' => true,
                ],
                [
                    'class' => DataObject::class,
                    'method' => 'requireDefaultRecords',
                    'isFirst' => true,
                ],
            ]
        );
    }
}
