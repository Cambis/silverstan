<?php

declare(strict_types=1);

namespace Cambis\Silverstan\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;

/**
 * This visitor marks implementors of `SilverStripe\Dev\TestOnly` to make them easy to find.
 */
final class TestOnlyFinderVisitor extends NodeVisitorAbstract
{
    public const ATTRIBUTE_KEY = 'isTestOnly';

    /**
     * @return null
     */
    #[Override]
    public function enterNode(Node $node)
    {
        if (!$node instanceof ClassLike) {
            return null;
        }

        if (($node instanceof Class_ || $node instanceof Enum_) && $this->doNamesContainTestOnly($node->implements)) {
            $node->setAttribute(self::ATTRIBUTE_KEY, true);

            return null;
        }

        if ($node instanceof Interface_ && $this->doNamesContainTestOnly($node->extends)) {
            $node->setAttribute(self::ATTRIBUTE_KEY, true);

            return null;
        }

        return null;
    }

    /**
     * @param Name[] $stmts
     */
    private function doNamesContainTestOnly(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if ($stmt->toString() !== 'SilverStripe\\Dev\\TestOnly') {
                continue;
            }

            return true;
        }

        return false;
    }
}
