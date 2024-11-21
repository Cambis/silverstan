# @cambis/silverstan

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
