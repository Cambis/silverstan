# @cambis/silverstan

## 2.1.6

### Patch Changes

- 6c9a3f8: Allow autoloading of modules with no config files
- bd8eee9: Allow case insensitive method calls in ExtensibleClassReflectionExtension

## 2.1.5

### Patch Changes

- 4d0204a: update Injector::get to use correct definition for constructorArgs

## 2.1.4

### Patch Changes

- bd3aa9e: Allow property assign in DisallowPropertyFetchOnConfigForClassRule
- fa2a340: Add status badges

## 2.1.3

### Patch Changes

- be25e75: Check for constructor in RequireInjectableCreateToMatchConstructorSignatureRule

## 2.1.2

### Patch Changes

- 53f3905: Add RequireInjectableCreateToMatchConstructorSignatureRule (bleeding edge)

## 2.1.1

### Patch Changes

- e405d61: Add stub for SiteTree
- 5496707: Update stub files

## 2.1.0

### Minor Changes

- 6ffe44b: Update error identifiers
- 6ffe44b: Move DisallowUsageOfDeprecatedConfigurationPropertyRule out of bleeding edge

## 2.0.3

### Patch Changes

- 822632a: Cache resolved configuration properties

## 2.0.2

### Patch Changes

- 7bf485c: Add DisallowUsageOfDeprecatedConfigurationPropertyRule (Bleeding edge)

## 2.0.1

### Patch Changes

- bab7e57: Update workflows to run on 1.x
- 20c95d7: update FileFinder::getAppRootDirectory() priority

## 2.0.0

### Major Changes

- c5f2cfa: PHPStan 2.0 upgrade

### Minor Changes

- e10e659: Report usage of deprecated DataObject::getCMSValidator() method

### Patch Changes

- 04bf121: Update stubs and test cases

## 1.1.0

### Minor Changes

- f401934: Add preliminary support for Silverstripe 6.0

### Patch Changes

- 8f07cf8: Add fallback directory for `FileFinder::getAppRootDirectory()`

## 1.0.7

### Patch Changes

- c4dc024: Set reflection property to accessible in PrivateStaticMiddleware

## 1.0.6

### Patch Changes

- c567bae: Update stubs for ClassInfo, Extensible and Versioned

## 1.0.5

### Patch Changes

- 66fbc88: - Add proper support for silverstripe/config:^1.4
  - Update e2e configuration to test multiple versions

## 1.0.4

### Patch Changes

- Check realpath and unaltered path, update e2e tests.

## 1.0.3

### Patch Changes

- 79776b4: Prevent double inclusion of vendormodule directories

## 1.0.2

### Patch Changes

- 285305e: Explicitly require classes in Autoloader

## 1.0.1

### Patch Changes

- 1195a33: Update db stubs

## 1.0.0

### Major Changes

- 8ab62f8: - Add custom autoloader and configuration resolution. The intention here is to allow this extension to not be tied to any specific Silverstripe version. This will also allow us to use PHPStan 2.0 with Silverstripe 5.x, currently the two are not compatitable. These features are currently opt-in using the bleedingEdge config but will become standard in 2.0
  - Various bugfixes

### Minor Changes

- b04375c: Add generic stubs for DBField
- fa49499: Add normaliser service

### Patch Changes

- 0c2d0c6: Update bitmask values
- d45a6fb: Add support for multi-relational has one
- 93162dd: Remove Extension type resolvers
- 8798233: Fallback to ObjectType in TypeResolver::resolveDBFieldType()
- e3dccc0: Resolve readable and writable types for db fields
- 638b255: Remove FixedFieldsPropertyTypeResolver

## 0.5.4

### Patch Changes

- 5003ae8: Resolve db field type from getter if present

## 0.5.3

### Patch Changes

- 8af6e0d: Add stubs for SilverStripe\Control\HTTPRequest and SilverStripe\Core\Convert
- 39d085f: Allow this calls in ConfigForClassGetReturnTypeExtension

## 0.5.2

### Patch Changes

- 1755764: Catch for mixed type in DisallowPropertyFetchOnConfigForClassRule

## 0.5.1

### Patch Changes

- 615e69b: Fix for DisallowPropertyFetchOnConfigForClassRule

## 0.5.0

### Minor Changes

- b17bd5a: Add ResponsiveImageSetsMethodReflectionResolver

## 0.4.0

### Minor Changes

- da4d412: Update test case structure to be inline with common phpstan test cases

### Patch Changes

- f042f2c: Trim db field names when resolving their types

## 0.3.1

### Patch Changes

- 69e1025: Accept parent and subclasses of `SilverStripe\View\ViewableData` in ViewableDataClassReflectionExtension

## 0.3.0

### Minor Changes

- baa4268:
  - Add InjectorGetReturnTypeExtension
  - Add SingletonReturnTypeExtension
  - Add DataObjectDbObjectReturnTypeExtension

## 0.2.0

### Minor Changes

- 1c0601a:
  - Clean up resolvers and factory implementation
  - Update README with more information on custom analysis
- e5b764a: Add support for unclecheese/display-logic

## 0.1.1

### Patch Changes

- Don't check for native configuration property when resolving all types

## 0.1.0

### Minor Changes

- Initial release
