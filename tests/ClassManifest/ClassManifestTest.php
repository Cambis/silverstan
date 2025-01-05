<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\ClassManifest;

use Cambis\Silverstan\ClassManifest\ClassManifest;
use Cambis\Silverstan\FileCleaner\FileCleaner;
use Cambis\Silverstan\FileFinder\FileFinder;
use Cambis\Silverstan\NodeVisitor\TestOnlyFinderVisitor;
use Cambis\Silverstan\Tests\ClassManifest\Source\Foo;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Override;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor\NameResolver;
use PHPStan\Testing\PHPStanTestCase;

final class ClassManifestTest extends PHPStanTestCase
{
    public function testExcludeTestOnly(): void
    {
        $classManifest = new ClassManifest(
            self::getContainer()->getByType(ClassMapGenerator::class),
            [],
            self::getContainer()->getByType(FileCleaner::class),
            self::getContainer()->getByType(FileFinder::class),
            self::getContainer()->getByType(NameResolver::class),
            self::getContainer()->getByType(NodeFinder::class),
            false,
            /** @phpstan-ignore argument.type */
            self::getContainer()->getService('currentPhpVersionPhpParser'),
            self::getContainer()->getByType(TestOnlyFinderVisitor::class),
        );

        $this->assertFalse($classManifest->hasClass(Foo::class));

        $classManifest->addClass(Foo::class, __DIR__ . '/Source/Foo.php');

        $this->assertFalse($classManifest->hasClass(Foo::class));
    }

    public function testIncludeTestOnly(): void
    {
        $classManifest = new ClassManifest(
            self::getContainer()->getByType(ClassMapGenerator::class),
            [],
            self::getContainer()->getByType(FileCleaner::class),
            self::getContainer()->getByType(FileFinder::class),
            self::getContainer()->getByType(NameResolver::class),
            self::getContainer()->getByType(NodeFinder::class),
            true,
            /** @phpstan-ignore argument.type */
            self::getContainer()->getService('currentPhpVersionPhpParser'),
            self::getContainer()->getByType(TestOnlyFinderVisitor::class),
        );

        $this->assertTrue($classManifest->hasClass(Foo::class));
    }

    /**
     * @return string[]
     */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../tests.neon'];
    }
}
