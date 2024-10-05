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
    ) {
    }

    #[Override]
    public function getConfigurationPropertyName(): string
    {
        return 'fixed_fields';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $fixedFields = [
            'Title' => 'SilverStripe\ORM\FieldType\DBVarchar',
            'OldID' => 'SilverStripe\ORM\FieldType\DBInt',
            'ObsoleteClassName' => 'SilverStripe\ORM\FieldType\DBClassName',
            ...(array) $this->configurationResolver->get('SilverStripe\ORM\DataObject', $this->getConfigurationPropertyName()),
        ];

        $types = [];

        /** @var class-string[] $fixedFields */
        foreach ($fixedFields as $fieldName => $fieldType) {
            $types[$fieldName] = $this->typeResolver->resolveDBFieldType('SilverStripe\ORM\DataObject', $fieldName, $fieldType);
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
