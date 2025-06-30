<?php

declare(strict_types=1);

namespace Cambis\Silverstan\ReflectionResolver\ReflectionResolver;

use Cambis\Silverstan\Reflection\MethodReflection\ExtensibleMethodReflection;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\ReflectionResolver\Contract\MethodReflectionResolverInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
use function strtolower;

final readonly class SimpleRelationMethodReflectionResolver implements MethodReflectionResolverInterface
{
    public function __construct(
        private string $configurationPropertyName,
        private ClassReflectionAnalyser $classReflectionAnalyser,
        private TypeResolver $typeResolver
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return $this->configurationPropertyName;
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $types = $this->typeResolver->resolveInjectedMethodTypesFromConfigurationProperty($classReflection, $this->configurationPropertyName);

        $methodReflections = [];

        foreach ($types as $name => $type) {
            $methodReflections[strtolower($name)] = new ExtensibleMethodReflection($name, $classReflection, $type, [], false, false, null, TemplateTypeMap::createEmpty());
        }

        return $methodReflections;
    }
}
