<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Type\TypeSpecifyingExtension;

use Override;
use PHPStan\Testing\TypeInferenceTestCase;

final class ViewableDataHasFieldTypeSpecifyingExtensionTest extends TypeInferenceTestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/Fixture/ViewableDataHasFieldTypes.php');
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
