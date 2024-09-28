<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Extension\Reflection;

use Generator;
use Override;
use PHPStan\Testing\TypeInferenceTestCase;

final class ViewableDataClassReflectionExtensionTest extends TypeInferenceTestCase
{
    public function typeFileAsserts(): Generator
    {
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/ViewableDataPropertyReflections.php');
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
        return [__DIR__ . '/../../../extension.neon'];
    }
}
