<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ClassManifest;

use Cambis\Silverstan\FileCleaner\FileCleaner;
use Cambis\Silverstan\FileFinder\FileFinder;
use Cambis\Silverstan\NodeVisitor\TestOnlyFinderVisitor;
use Composer\ClassMapGenerator\ClassMap;
use Composer\ClassMapGenerator\ClassMapGenerator;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use function array_key_exists;
use function str_starts_with;
use function strcmp;
use function strtolower;
use function uasort;

/**
 * @see \Cambis\Silverstan\Tests\ClassManifest\ClassManifestTest
 */
final class ClassManifest
{
    /**
     * A map of lowercase class names to proper class names.
     *
     * @var array<lowercase-string, class-string>
     */
    private array $classes = [];

    private readonly ClassMap $classMap;

    public function __construct(
        private readonly ClassMapGenerator $classMapGenerator,
        /**
         * A list of classes that should be excluded from the manifest.
         *
         * @var list<class-string>
         */
        private readonly array $excludedClasses,
        private readonly FileCleaner $fileCleaner,
        private readonly FileFinder $fileFinder,
        private readonly NameResolver $nameResolver,
        private readonly NodeFinder $nodeFinder,
        private readonly bool $includeTestOnly,
        private readonly Parser $parser,
        private readonly TestOnlyFinderVisitor $testOnlyFinderVisitor,
    ) {
        $this->classMap = $this->generateClassMap();

        foreach ($this->classMap->map as $className => $path) {
            $this->addClass($className, $path);
        }

        // Register `Page` if it does not exist
        if (!$this->hasClass('Page')) {
            $this->addClass('Page', __DIR__ . '/../../stubs/Page.php');
        }

        // Register `PageController` if it does not exist
        if (!$this->hasClass('PageController')) {
            $this->addClass('PageController', __DIR__ . '/../../stubs/PageController.php');
        }

        // Remove excluded classes
        foreach ($this->excludedClasses as $excludedClass) {
            if (!$this->hasClass($excludedClass)) {
                continue;
            }

            $this->removeClass($excludedClass);
        }

        $this->sort();
    }

    /**
     * @param class-string $className
     */
    public function hasClass(string $className): bool
    {
        return array_key_exists(strtolower($className), $this->classes);
    }

    /**
     * @param class-string $className
     * @param non-empty-string $path
     */
    public function addClass(string $className, string $path): static
    {
        if (!$this->classMap->hasClass($className)) {
            $this->classMap->addClass($className, $path);
        }

        $this->classes[strtolower($className)] = $className;

        if ($this->includeTestOnly) {
            return $this;
        }

        foreach ($this->findTestOnlyClasses($path) as $testOnlyClass) {
            if (!$this->hasClass($testOnlyClass)) {
                continue;
            }

            $this->removeClass($testOnlyClass);
        }

        return $this;
    }

    /**
     * @param class-string $className
     */
    public function removeClass(string $className): static
    {
        unset($this->classMap->map[$className]);
        unset($this->classes[strtolower($className)]);

        return $this;
    }

    /**
     * A map of lowercase class names to proper class names.
     *
     * @api
     *
     * @return array<lowercase-string, class-string>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param class-string $className
     */
    public function getClassPath(string $className): string
    {
        $classEntry = $this->classes[strtolower($className)];

        return $this->classMap->getClassPath($classEntry);
    }

    /**
     * Sort the classes by the following priority
     *
     * - SilverStripe\\...
     * - ...everyting else
     */
    public function sort(): void
    {
        uasort($this->classes, static function (string $a, string $b): int {
            if (str_starts_with($a, 'SilverStripe\\') && !str_starts_with($b, 'SilverStripe\\')) {
                return -1;
            }

            if (!str_starts_with($a, 'SilverStripe\\') && str_starts_with($b, 'SilverStripe\\')) {
                return 1;
            }

            return strcmp($a, $b);
        });
    }

    private function generateClassMap(): ClassMap
    {
        // Generate the class map
        $this->classMapGenerator->scanPaths(
            $this->fileFinder->getPhpFiles()
        );

        return $this->classMapGenerator->getClassMap();
    }

    /**
     * Get a list of classes in a file that implement `SilverStripe\Dev\TestOnly`.
     *
     * @return list<class-string>
     */
    private function findTestOnlyClasses(string $path): array
    {
        // Strip out all unecessary content from the file
        $contents = $this->fileCleaner->cleanFile($path);
        $stmts = $this->parser->parse($contents) ?? [];

        if ($stmts === []) {
            return [];
        }

        $testOnlyClasses = [];

        // Create a new traverser and register our visitors
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->nameResolver);
        $traverser->addVisitor($this->testOnlyFinderVisitor);

        $stmts = $traverser->traverse($stmts);

        // Grab all class like nodes
        $classLikes = $this->nodeFinder->findInstanceOf($stmts, ClassLike::class);

        // Iterate over nodes and check for implementors of `SilverStripe\Dev\TestOnly`
        foreach ($classLikes as $classLike) {
            if (!$classLike instanceof ClassLike) {
                continue;
            }

            // Class is missing namespaced name, skip
            if ($classLike->namespacedName === null) {
                continue;
            }

            // Class is not implementor of `SilverStripe\Dev\TestOnly`, skip
            if (!$classLike->hasAttribute(TestOnlyFinderVisitor::ATTRIBUTE_KEY)) {
                continue;
            }

            /** @var class-string $className */
            $className = $classLike->namespacedName->toString();

            // Add implementor of `SilverStripe\Dev\TestOnly` to list
            $testOnlyClasses[] = $className;
        }

        return $testOnlyClasses;
    }
}
