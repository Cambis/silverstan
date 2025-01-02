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
use function str_starts_with;
use function strcmp;
use function uksort;

final readonly class ClassManifest
{
    public ClassMap $classMap;

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

        // Sort the class map so that `SilverStripe` classes have priority
        uksort($classMap->map, static function (string $a, string $b): int {
            if (str_starts_with($a, 'SilverStripe\\') && !str_starts_with($b, 'SilverStripe\\')) {
                return -1;
            }

            if (!str_starts_with($a, 'SilverStripe\\') && str_starts_with($b, 'SilverStripe\\')) {
                return 1;
            }

            return strcmp($a, $b);
        });

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
}
