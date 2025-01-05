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
use function array_keys;
use function strtolower;

final readonly class ClassManifest
{
    /**
     * A map of lowercase class names to proper class names.
     *
     * @api
     *
     * @var array<lowercase-string, class-string>
     */
    public array $classes;

    private ClassMap $classMap;

    public function __construct(
        private ClassMapGenerator $classMapGenerator,
        /**
         * A list of classes that should be excluded from the manifest.
         *
         * @var list<class-string>
         */
        private array $excludedClasses,
        private FileCleaner $fileCleaner,
        private FileFinder $fileFinder,
        private NameResolver $nameResolver,
        private NodeFinder $nodeFinder,
        private bool $includeTestOnly,
        private Parser $parser,
        private TestOnlyFinderVisitor $testOnlyFinderVisitor,
    ) {
        $this->classMap = $this->generateClassMap();
        $this->classes = $this->generateClasses();
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
     */
    public function getClassPath(string $className): string
    {
        $classEntry = $this->classes[strtolower($className)];

        return $this->classMap->getClassPath($classEntry);
    }

    private function generateClassMap(): ClassMap
    {
        // Generate the class map
        $this->classMapGenerator->scanPaths(
            $this->fileFinder->getPhpFiles()
        );

        $classMap = $this->classMapGenerator->getClassMap();

        // Register `Page` if it does not exist
        if (!$classMap->hasClass('Page')) {
            $classMap->addClass('Page', __DIR__ . '/../../stubs/Page.php');
        }

        // Register `PageController` if it does not exist
        if (!$classMap->hasClass('PageController')) {
            $classMap->addClass('PageController', __DIR__ . '/../../stubs/PageController.php');
        }

        // Remove excluded classes
        foreach ($this->excludedClasses as $excludedClass) {
            if (!$classMap->hasClass($excludedClass)) {
                continue;
            }

            unset($classMap->map[$excludedClass]);
        }

        // No further processing is needed, return
        if ($this->includeTestOnly) {
            return $classMap;
        }

        // Iterate over class map and remove implementors of `SilverStripe\Dev\TestOnly`
        foreach ($classMap->map as $path) {
            // Strip out all unecessary content from the file
            $contents = $this->fileCleaner->cleanFile($path);

            $stmts = $this->parser->parse($contents) ?? [];

            if ($stmts === []) {
                continue;
            }

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

                // Remove implementor of `SilverStripe\Dev\TestOnly` from the class map
                unset($classMap->map[$classLike->namespacedName->toString()]);
            }
        }

        return $classMap;
    }

    /**
     * @return array<lowercase-string, class-string>
     */
    private function generateClasses(): array
    {
        $classes = [];

        foreach (array_keys($this->classMap->map) as $className) {
            $classes[strtolower($className)] = $className;
        }

        return $classes;
    }
}
