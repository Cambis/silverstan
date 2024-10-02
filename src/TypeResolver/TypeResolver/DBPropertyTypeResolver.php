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
use SilverStripe\ORM\FieldType\DBField;
use function is_array;

final class DBPropertyTypeResolver implements PropertyTypeResolverInterface, TypeResolverAwareInterface
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
        return 'db';
    }

    #[Override]
    public function resolve(ClassReflection $classReflection): array
    {
        if (!$this->classReflectionAnalyser->isDataObject($classReflection)) {
            return [];
        }

        $properties = [];

        $db = $this->configurationResolver->get($classReflection->getName(), 'db');

        if (!is_array($db) || $db === []) {
            return $properties;
        }

        /** @var class-string<DBField>[] $db */
        foreach ($db as $fieldName => $fieldType) {
            $properties[$fieldName] = $this->typeResolver->resolveDBFieldType($classReflection->getName(), $fieldName, $fieldType);
        }

        return $properties;
    }

    #[Override]
    public function setTypeResolver(TypeResolver $typeResolver): static
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
