<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\Extension\PhpDoc;

use Generator;
use PHPStan\Testing\TypeInferenceTestCase;

final class ExtensionOwnerTypeNodeResolverExtensionTest extends TypeInferenceTestCase
{
    public function typeFileAsserts(): Generator
    {
        yield from $this->gatherAssertTypes(__DIR__ . '/Fixture/extension-types.php.inc');
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
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }
}
