<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Reflection\Deprecation\MethodDeprecationExtension;

use Cambis\Silverstan\Tests\Reflection\Deprecation\MethodDeprecationExtension\Source\Model\Foo;
use Override;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Testing\PHPStanTestCase;

final class DataObjectGetCMSValidatorMethodDeprecationExtensionTest extends PHPStanTestCase
{
    public function testDeprecation(): void
    {
        $reflectionProvider = self::createReflectionProvider();
        $fixtureClass = $reflectionProvider->getClass(Foo::class);

        $this->assertTrue($fixtureClass->getMethod('getCMSValidator', new OutOfClassScope())->isDeprecated()->yes());
        $this->assertSame(
            'use Cambis\Silverstan\Tests\Reflection\Deprecation\MethodDeprecationExtension\Source\Model\Foo::getCMSCompositeValidator() instead. See https://docs.silverstripe.org/en/5/developer_guides/forms/validation/#validation-in-the-cms.',
            $fixtureClass->getMethod('getCMSValidator', new OutOfClassScope())->getDeprecatedDescription()
        );
    }

    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../tests.neon'];
    }
}
