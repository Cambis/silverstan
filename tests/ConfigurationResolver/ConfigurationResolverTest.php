<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Tests\ConfigurationResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Tests\ConfigurationResolver\Source\Model\Bar;
use Cambis\Silverstan\Tests\ConfigurationResolver\Source\Model\Foo;
use Override;
use PHPStan\Testing\PHPStanTestCase;

final class ConfigurationResolverTest extends PHPStanTestCase
{
    private readonly ConfigurationResolver $configurationResolver;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationResolver = self::getContainer()->getByType(ConfigurationResolver::class);
    }

    public function testGetAll(): void
    {
        $this->assertSame(
            [
                'first' => ['test_1'],
                'second' => ['test_1'],
                'third' => ['test_1'],
            ],
            $this->configurationResolver->get(Foo::class, null)
        );
    }

    public function testGetInherited(): void
    {
        $this->assertSame(
            ['test_1', 'test_2'],
            $this->configurationResolver->get(Bar::class, 'first')
        );
    }

    public function testGetUninherited(): void
    {
        $this->assertSame(
            ['test_2'],
            $this->configurationResolver->get(Bar::class, 'first', ConfigurationResolver::EXCLUDE_INHERITED)
        );
    }

    public function testGetExtraSources(): void
    {
        $this->assertSame(
            ['test_1', 'test_3', 'test_2'],
            $this->configurationResolver->get(Bar::class, 'third')
        );
    }

    public function testGetExcludeMiddleware(): void
    {
        $this->assertNull($this->configurationResolver->get(Bar::class, 'third', true));
    }

    public function testGetExcludeExtraSources(): void
    {
        $this->assertSame(
            ['test_1', 'test_2'],
            $this->configurationResolver->get(Bar::class, 'third', ConfigurationResolver::EXCLUDE_EXTRA_SOURCES)
        );
    }

    public function testGetUninheritedExcludeExtraSources(): void
    {
        $this->assertSame(
            ['test_2'],
            $this->configurationResolver->get(Bar::class, 'third', ConfigurationResolver::EXCLUDE_INHERITED | ConfigurationResolver::EXCLUDE_EXTRA_SOURCES)
        );
    }

    public function testGetExtraConfig(): void
    {
        $this->assertSame(
            ['test_4'],
            $this->configurationResolver->get(Bar::class, 'fourth')
        );
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
