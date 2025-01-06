<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\ReflectionAnalyser\ClassReflectionAnalyser;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Cambis\Silverstan\TypeResolver\Contract\TypeResolverAwareInterface;
use Cambis\Silverstan\TypeResolver\TypeResolver;
use Override;
use PHPStan\Reflection\ClassReflection;

final class FixedFieldsPropertyTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
{
    private TypeResolver $typeResolver;

    public function __construct(
        private readonly ClassReflectionAnalyser $classReflectionAnalyser,
        private readonly ConfigurationResolver $configurationResolver,
        /**
         * @var true|int-mask-of<ConfigurationResolver::EXCLUDE_*>
         */
        private readonly true|int $excludeMiddleware = ConfigurationResolver::EXCLUDE_NONE
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'fixed_fields';
    }

    #[Override]
    public function getExcludeMiddleware(): true|int
    {
        return $this->excludeMiddleware;
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $fixedFields = (array) $this->configurationResolver->get('SilverStripe\ORM\DataObject', $this->getConfigurationPropertyName(), $this->excludeMiddleware);

        $types = [];

        /** @var class-string[] $fixedFields */
        foreach ($fixedFields as $fieldName => $fieldType) {
            $types[$fieldName] = $this->typeResolver->resolveDBFieldType($classReflection, $fieldName, $fieldType);
        }

        return $types;
    }

    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver): static
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
