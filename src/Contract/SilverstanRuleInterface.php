<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Contract;

use PhpParser\Node;
use PHPStan\Rules\Rule;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

/**
 * @template T of Node
 * @extends Rule<T>
 */
interface SilverstanRuleInterface extends Rule, DocumentedRuleInterface, ConfigurableRuleInterface
{
}
