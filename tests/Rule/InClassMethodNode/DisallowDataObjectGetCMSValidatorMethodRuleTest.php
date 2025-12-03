<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\InClassMethodNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\Rule\InClassMethodNode\DisallowDataObjectGetCMSValidatorMethodRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowDataObjectGetCMSValidatorMethodRule>
 */
final class DisallowDataObjectGetCMSValidatorMethodRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowGetCMSValidator.php'], [
            [
                'Declaration of deprecated method getCMSValidator() on class Cambis\Silverstan\Tests\Rule\InClassMethodNode\Fixture\DisallowGetCMSValidator: 5.4.0 override Cambis\Silverstan\Tests\Rule\InClassMethodNode\Fixture\DisallowGetCMSValidator::getCMSCompositeValidator() instead.',
                10,
                'See https://docs.silverstripe.org/en/developer_guides/forms/validation/#validation-in-the-cms.',
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

    protected function getRule(): Rule
    {
        return new DisallowDataObjectGetCMSValidatorMethodRule(
            self::getContainer()->getByType(ClassReflectionAnalyser::class),
        );
    }
}
