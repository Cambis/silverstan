<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Reflection\MethodReflection\ExtensibleMethodReflection;
use Cambis\Silverstan\Reflection\ParameterReflection\ExtensibleParameterReflection;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use function is_array;

/**
 * Resolves magic comparison methods from `UncleCheese\DisplayLogic\Criteria`.
 *
 * @see \Cambis\Silverstan\Tests\Extension\Reflection\ExtensibleClassReflectionExtensionTest
 */
final readonly class DisplayLogicCriteriaComparisonsMethodReflectionResolver implements MethodReflectionResolverInterface
{
    public function __construct(
        private ConfigurationResolver $configurationResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'comparisons';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$classReflection->is('UncleCheese\DisplayLogic\Criteria')) {
            return [];
        }

        $methodReflections = [];

        $comparisons = $this->configurationResolver->get($classReflection->getName(), $this->getConfigurationPropertyName());

        if (!is_array($comparisons) || $comparisons === []) {
            return [];
        }

        $parameters = [new ExtensibleParameterReflection('val', new MixedType(), PassedByReference::createNo(), true, true, new NullType())];
        $returnType = new ObjectType('UncleCheese\DisplayLogic\Criteria');

        /** @var string[] $comparisons */
        foreach ($comparisons as $comparison) {
            $methodReflections[$comparison] = new ExtensibleMethodReflection($comparison, $classReflection, $returnType, $parameters, false, true, null, TemplateTypeMap::createEmpty());
        }

        return $methodReflections;
    }
}
