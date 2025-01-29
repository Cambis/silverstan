<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Reflection\ClassReflectionExtension;

use Override;
use PHPStan\Testing\TypeInferenceTestCase;

final class ExtensibleClassReflectionExtensionTest extends TypeInferenceTestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/DBPropertyReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/DependencyInjectionPropertyReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/DisplayLogicMethodReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/ExtensionMethodReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/ExtensionPropertyReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/ResponsiveImagesMethodReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/SimpleRelationMethodReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/SimpleRelationPropertyReflections.php');
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/ManyRelationMethodReflections.php');
    }

    /**
     * @dataProvider dataFileAsserts
     */
    public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    /**
     * @return string[]
     */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../tests.neon'];
    }
}
