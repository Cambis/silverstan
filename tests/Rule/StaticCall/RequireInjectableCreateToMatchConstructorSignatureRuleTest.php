<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\StaticCall;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\Rule\StaticCall\RequireInjectableCreateToMatchConstructorSignatureRule;
use Override;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RequireInjectableCreateToMatchConstructorSignatureRule>
 */
final class RequireInjectableCreateToMatchConstructorSignatureRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/RequireInjectableCreateToMatch.php'], [
            [
                'Method Cambis\Silverstan\Tests\Rule\StaticCall\Source\Foo::create() invoked with 0 parameters, 2 required.',
                9,
            ],
            [
                'Parameter #1 $param1 of method Cambis\Silverstan\Tests\Rule\StaticCall\Source\Foo::create() expects string, int given.',
                11,
            ],
            [
                'Missing parameter $param2 (int) in call to method Cambis\Silverstan\Tests\Rule\StaticCall\Source\Foo::create().',
                13,
            ],
            [
                'Parameter $param1 of method Cambis\Silverstan\Tests\Rule\StaticCall\Source\Foo::create() expects string, int given.',
                13,
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
        return new RequireInjectableCreateToMatchConstructorSignatureRule(
            self::getContainer()->getByType(ClassReflectionAnalyser::class),
            self::getContainer()->getByType(FunctionCallParametersCheck::class) // @phpstan-ignore phpstanApi.classConstant
        );
    }
}
