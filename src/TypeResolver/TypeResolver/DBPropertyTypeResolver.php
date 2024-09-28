<?php

declare(strict_types=1);

namespace Cambis\Silverstan\TypeResolver\TypeResolver;

use Cambis\Silverstan\ConfigurationResolver\ConfigurationResolver;
use Cambis\Silverstan\InjectionResolver\InjectionResolver;
use Cambis\Silverstan\TypeResolver\Contract\PropertyTypeResolverInterface;
use Override;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use function is_array;

final readonly class DBPropertyTypeResolver implements PropertyTypeResolverInterface
{
    /**
     * @var array<class-string<DBField>, class-string<Type>>
     */
    private const DBFIELD_TO_TYPE_MAPPING = [
        'SilverStripe\ORM\FieldType\DBBoolean' => BooleanType::class,
        'SilverStripe\ORM\FieldType\DBDecimal' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBFloat' => FloatType::class,
        'SilverStripe\ORM\FieldType\DBInt' => IntegerType::class,
    ];

    public function __construct(
        private ConfigurationResolver $configurationResolver,
        private InjectionResolver $injectionResolver,
        private ReflectionProvider $reflectionProvider
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
        $properties = [];

        $db = $this->configurationResolver->get($classReflection->getName(), 'db');

        if (!is_array($db) || $db === []) {
            return $properties;
        }

        /** @var class-string<DBField>[] $db */
        foreach ($db as $fieldName => $fieldType) {
            $properties[$fieldName] = $this->resolveDBFieldType($classReflection->getName(), $fieldName, $fieldType);
        }

        return $properties;
    }

    /**
     * @param class-string $className
     * @param class-string<DBField> $fieldType
     */
    private function resolveDBFieldType(string $className, string $fieldName, string $fieldType): Type
    {
        /** @var DBField $field */
        $field = $this->injectionResolver->create($fieldType, 'Temp');
        $classReflection = $this->reflectionProvider->getClass($field::class);

        foreach (self::DBFIELD_TO_TYPE_MAPPING as $dbClass => $type) {
            if (!$this->reflectionProvider->hasClass($dbClass)) {
                continue;
            }

            if (!$classReflection->is($dbClass)) {
                continue;
            }

            return new $type();
        }

        // Instantiate the object so we can check for required fields
        $object = $this->injectionResolver->create($className);

        // Fallback case
        if (!$object instanceof DataObject && !$object instanceof Extension) {
            return new StringType();
        }

        // If the object is an extension, create a mock DataObject and add the extension to it
        if ($object instanceof Extension) {
            $object = new class extends DataObject implements TestOnly {};
            $object::add_extension($className);
        }

        // Check if the field is required
        if ($object->getCMSCompositeValidator()->fieldIsRequired($fieldName)) {
            return new StringType();
        }

        // This is not required and therefore is nullable
        return TypeCombinator::addNull(new StringType());
    }
}
