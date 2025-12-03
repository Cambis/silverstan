<?php

declare(strict_types=1);

namespace Cambis\Silverstan\Rule\InClassMethodNode;

use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;
use function strtolower;

/**
 * @implements Rule<InClassMethodNode>
 *
 * @see \Cambis\Silverstan\Tests\Rule\InClassMethodNode\DisallowDataObjectGetCMSValidatorMethodRuleTest
 */
final readonly class DisallowDataObjectGetCMSValidatorMethodRule implements Rule
{
    /**
     * @var string
     */
    private const IDENTIFIER = 'silverstan.method.deprecated';

    public function __construct(
        private ClassReflectionAnalyser $classReflectionAnalyser,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $prototype = $node->getMethodReflection();

        if (strtolower($prototype->getName()) !== 'getcmsvalidator') {
            return [];
        }

        // Don't report if `getCMSCompositeValidator()` does not exist, as we are probably on an older version of Silverstripe
        if (!$classReflection->hasMethod('getCMSCompositeValidator')) {
            return [];
        }

        $message = sprintf(
            'Declaration of deprecated method getCMSValidator() on class %s: 5.4.0 override %s::getCMSCompositeValidator() instead.',
            $classReflection->getName(),
            $classReflection->getName(),
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->addTip('See https://docs.silverstripe.org/en/developer_guides/forms/validation/#validation-in-the-cms.')
                ->build(),
        ];
    }
}
