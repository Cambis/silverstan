<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\New_;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\Rule\New_\DisallowNewInstanceOnInjectableRule;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DisallowNewInstanceOnInjectableRule>
 */
final class DisallowNewInstanceOnInjectableRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/DisallowNewInstance.php'], [
            [
                'Use Cambis\Silverstan\Tests\Rule\New_\Source\InjectableClass::create() instead of new Cambis\Silverstan\Tests\Rule\New_\Source\InjectableClass().',
                13,
            ],
        ]);
    }

    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../../extension.neon',
        ];
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new DisallowNewInstanceOnInjectableRule(
            self::getContainer()->getByType(ClassReflectionAnalyser::class),
            self::getContainer()->getByType(ReflectionProvider::class)
        );
    }
}
