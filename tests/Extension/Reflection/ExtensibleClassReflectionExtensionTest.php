<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Extension\Reflection;

use Generator;
use Override;
use PHPStan\Testing\TypeInferenceTestCase;

final class ExtensibleClassReflectionExtensionTest extends TypeInferenceTestCase
{
    public function typeFileAsserts(): Generator
    {
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/DBPropertyReflections.php');
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/ExtensionMethodReflections.php');
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/ExtensionPropertyReflections.php');
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/SimpleRelationMethodReflections.php');
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/SimpleRelationPropertyReflections.php');
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/ManyRelationMethodReflections.php');
    }

    /**
     * @dataProvider typeFileAsserts
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
