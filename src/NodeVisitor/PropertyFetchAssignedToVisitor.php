<?php

declare(strict_types=1);

namespace Cambis\Silverstan\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\NodeVisitorAbstract;

final class PropertyFetchAssignedToVisitor extends NodeVisitorAbstract
{
    public const ATTRIBUTE_KEY = 'isBeingAssigned';

    #[Override]
    public function enterNode(Node $node): ?Node
    {
        if (!$node instanceof Assign && !$node instanceof AssignOp && !$node instanceof AssignRef) {
            return null;
        }

        if (!$node->var instanceof PropertyFetch) {
            return null;
        }

        $node->var->setAttribute(self::ATTRIBUTE_KEY, true);

        return null;
    }
}
