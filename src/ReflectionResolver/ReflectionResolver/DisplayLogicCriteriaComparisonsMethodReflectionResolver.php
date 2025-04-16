<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\Reflection\ExtensibleMethodReflection;
use Cambis\Silverstan\Reflection\ExtensibleParameterReflection;
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
final class DisplayLogicCriteriaComparisonsMethodReflectionResolver implements MethodReflectionResolverInterface
{
    /**
     * @readonly
     */
    private ConfigurationResolver $configurationResolver;
    public function __construct(ConfigurationResolver $configurationResolver)
    {
        $this->configurationResolver = $configurationResolver;
    }

    public function getConfigurationPropertyName(): string
    {
        return 'comparisons';
    }

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
