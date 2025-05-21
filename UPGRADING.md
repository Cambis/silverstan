# Upgrading

## From 2.0 to 2.1

### Updates to error identifiers
Some of the error identifiers have been updated, please refer to the table below. 

|Original name|New name|
|---|---|
|silverstan.configurationProperty.isDeprecated|silverstan.configurationProperty.deprecated|
|silverstan.invalidConfigurationProperty|silverstan.configurationProperty.invalid|
|silverstan.newInjectable|silverstan.injectable.useCreate|
|silverstan.requiredConfigurationProperty|silverstan.configurationProperty.required|
|silverstan.unsafeConfigurationPropertyAccess|silverstan.configurationProperty.unsafe|
|silverstan.unsafeDataObjectAccess|silverstan.dataObject.unsafe|

## From 1.x to 2.x
Firstly, check the upgrade guide for PHPStan 2.0 [here](https://github.com/phpstan/phpstan/blob/2.0.x/UPGRADING.md).

### New autoloader and config collection
Silverstan now uses its own custom autoloader and config collection, previously it bootstrapped a Silverstripe application in order to autoload classes and gain access to the config collection.

This change allows us to not be tied to a specific Silverstripe version. Silverstripe and PHPStan also have some conflicting dependencies, this change allows us to avoid those.

You can opt-in to these new features in 1.x via the bleedingEdge config.
```neon
includes:
  - vendor/cambis/silverstan/bleedingEdge.neon
```

### Update to allowed unsafe DataObject method calls

The configuration for `silverstan.disallowMethodCallOnUnsafeDataObject.allowedMethodCalls` has been updated to include the class name as well.

#### 1.x
```neon
parameters:
  silverstan:
    disallowMethodCallOnUnsafeDataObject:
      allowedMethodCalls:
        - mySafeMethod
```

#### 2.x
```neon
parameters:
  silverstan:
    disallowMethodCallOnUnsafeDataObject:
      allowedMethodCalls:
        App\Model\Foo:
          - mySafeMethod
```

### Renamed classes
Many classes were renamed in order to better reflect the classes they were extending. Be aware of this if you have a custom configuration.

|Original name|New name|
|---|---|
|Cambis\Silverstan\Extension\PhpDoc\SilverstripeStubFilesExtension|Cambis\Silverstan\PhpDoc\StubFilesExtension\SilverstripeStubFilesExtension|
|Cambis\Silverstan\Extension\PhpDoc\DataObjectTypeNodeResolverExtension|Cambis\Silverstan\PhpDoc\TypeNodeResolverExtension\DataObjectTypeNodeResolverExtension|
|Cambis\Silverstan\Extension\PhpDoc\ExtensionOwnerTypeNodeResolverExtension|Cambis\Silverstan\PhpDoc\TypeNodeResolverExtension\ExtensionOwnerTypeNodeResolverExtension|
|Cambis\Silverstan\Extension\Reflection\AnnotationClassReflectionExtension|Cambis\Silverstan\Reflection\ClassReflectionExtension\AnnotationClassReflectionExtension|
|Cambis\Silverstan\Extension\Reflection\ExtensibleClassReflectionExtension|Cambis\Silverstan\Reflection\ClassReflectionExtension\ExtensibleClassReflectionExtension|
|Cambis\Silverstan\Extension\Reflection\ViewableDataClassReflectionExtension|Cambis\Silverstan\Reflection\ClassReflectionExtension\ViewableDataClassReflectionExtension|
|Cambis\Silverstan\Reflection\ExtensibleMethodReflection|Cambis\Silverstan\Reflection\MethodReflection\ExtensibleMethodReflection|
|Cambis\Silverstan\Reflection\ExtensibleParameterReflection|Cambis\Silverstan\Reflection\ParameterReflection\ExtensibleParameterReflection|
|Cambis\Silverstan\Reflection\ExtensiblePropertyReflection|Cambis\Silverstan\Reflection\PropertyReflection\ExtensiblePropertyReflection|
|Cambis\Silverstan\Reflection\ViewableDataPropertyReflection|Cambis\Silverstan\Reflection\PropertyReflection\ViewableDataPropertyReflection|
|Cambis\Silverstan\Extension\ClassPropertiesNode\ConfigurationPropertiesExtension|Cambis\Silverstan\Rule\ClassPropertiesNode\ReadWritePropertiesExtension\ConfigurationPropertiesExtension|
|Cambis\Silverstan\Extension\Type\ConfigCollectionInterfaceGetReturnTypeExtension|Cambis\Silverstan\Type\DynamicReturnTypeExtension\ConfigCollectionInterfaceGetReturnTypeExtension|
|Cambis\Silverstan\Extension\Type\ConfigForClassGetReturnTypeExtension|Cambis\Silverstan\Type\DynamicReturnTypeExtension\ConfigForClassGetReturnTypeExtension|
|Cambis\Silverstan\Extension\Type\DataObjectDbObjectReturnTypeExtension|Cambis\Silverstan\Type\DynamicReturnTypeExtension\DataObjectDbObjectReturnTypeExtension|
|Cambis\Silverstan\Extension\Type\ExtensionGetOwnerReturnTypeExtension|Cambis\Silverstan\Type\DynamicReturnTypeExtension\ExtensionGetOwnerReturnTypeExtension|
|Cambis\Silverstan\Extension\Type\InjectorGetReturnTypeExtension|Cambis\Silverstan\Type\DynamicReturnTypeExtension\InjectorGetReturnTypeExtension|
|Cambis\Silverstan\Extension\Type\SingletonReturnTypeExtension|Cambis\Silverstan\Type\DynamicReturnTypeExtension\SingletonReturnTypeExtension|
|Cambis\Silverstan\Type\UnsafeObjectType|Cambis\Silverstan\Type\ObjectType\UnsafeObjectType|
|Cambis\Silverstan\Extension\Type\DataObjectDeleteTypeSpecifyingExtension|Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectDeleteTypeSpecifyingExtension|
|Cambis\Silverstan\Extension\Type\DataObjectExistsTypeSpecifyingExtension|Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectExistsTypeSpecifyingExtension|
|Cambis\Silverstan\Extension\Type\DataObjectWriteTypeSpecifyingExtension|Cambis\Silverstan\Type\TypeSpecifyingExtension\DataObjectWriteTypeSpecifyingExtension|
|Cambis\Silverstan\Extension\Type\ExtensibleHasExtensionTypeSpecifyingExtension|Cambis\Silverstan\Type\TypeSpecifyingExtension\ExtensibleHasExtensionTypeSpecifyingExtension|
|Cambis\Silverstan\Extension\Type\ExtensibleHasMethodTypeSpecifyingExtension|Cambis\Silverstan\Type\TypeSpecifyingExtension\ExtensibleHasMethodTypeSpecifyingExtension|
|Cambis\Silverstan\Extension\Type\ViewableDataHasFieldTypeSpecifyingExtension|Cambis\Silverstan\Type\TypeSpecifyingExtension\ViewableDataHasFieldTypeSpecifyingExtension|
