<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\CollectedDataNode;

use Cambis\Silverstan\Collector\Expr\MagicDataObjectCallCollector;
use Cambis\Silverstan\Contract\SilverstanRuleInterface;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\RuleErrorBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @implements SilverstanRuleInterface<CollectedDataNode>
 * @see \Cambis\Silverstan\Tests\Rule\CollectedDataNode\DisallowUnsafeAccessOfMagicDataObjectRuleTest
 */
final class DisallowUnsafeAccessOfMagicDataObjectRule implements SilverstanRuleInterface
{
    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use `instanceof` and `exists()` first before accessing any magic `\SilverStripe\ORM\DataObject` methods or properties as the object may not be present in the database. ' .
            'Enabling this rule will change the return type of `$has_one` and `$belongs_to` relationships from `\SilverStripe\ORM\DataObject` to `\SilverStripe\ORM\DataObject|null` in order to encourage the use of the `instanceof` check.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @method \SilverStripe\ORM\DataObject Bar()
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function doSomething(): string
    {
        return $this->Bar()->Title;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * @method \SilverStripe\ORM\DataObject Bar()
 */
final class Foo extends \SilverStripe\ORM\DataObject
{
    public function doSomething(): string
    {
        if ($this->Bar() instanceof \SilverStripe\ORM\DataObject && $this->Bar()->exists()) {
            return $this->Bar()->Title;
        }

        return '';
    }
}
CODE_SAMPLE
                    ,
                    [
                        'enabled' => true,
                    ]
                )],
        );
    }

    #[Override]
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @param CollectedDataNode $node
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $callData = $node->get(MagicDataObjectCallCollector::class);
        $errors = [];

        foreach ($callData as $file => $data) {
            $calls = $this->gatherCalls($data);

            foreach ($calls as $call) {
                $firstCall = $call[0];

                if ($firstCall[0] === 'exists') {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message(
                    'Call exists() first before accessing any magic \SilverStripe\ORM\DataObject methods or properties.'
                )
                    ->file($file)
                    ->line($firstCall[1])
                    ->tip('See https://api.silverstripe.org/5/SilverStripe/ORM/DataObject.html#method_exists')
                    ->build();
            }
        }

        return $errors;
    }

    /**
     * @param array<int, array{string, string, string, int}> $data
     * @return array<non-falsy-string, non-empty-array<int<0, max>, array{string, int}>>
     */
    private function gatherCalls(array $data): array
    {
        $gathered = [];

        foreach ($data as [$className, $classMethodName, $callName, $line]) {
            $gathered[$className . '::' . $classMethodName][] = [$callName, $line];
        }

        return $gathered;
    }
}
