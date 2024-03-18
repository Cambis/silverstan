<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Rule\MethodCall;

use Cambis\Silverstan\Rule\MethodCall\RequireInterfaceForExtensibleHookMethodRule;
use Override;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;

/**
 * @extends RuleTestCase<RequireInterfaceForExtensibleHookMethodRule>
 */
final class RequireInterfaceForExtensibleHookMethodRuleTest extends RuleTestCase
{
    #[Override]
    protected function getRule(): Rule
    {
        return new RequireInterfaceForExtensibleHookMethodRule(
            self::getContainer()->getByType(FileTypeMapper::class),
            self::getContainer()->getByType(ReflectionProvider::class),
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/Fixture/ExtensibleWithoutAnnotation.php'], [
            [
                'Cannot find interface definition for extensible hook updateTitle.',
                16,
                'Use the @phpstan-silverstripe-extend annotation to point an interface where the hook method is defined.',
            ],
        ]);
    }
}
