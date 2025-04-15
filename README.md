# Silverstan | Kaitiaki Ponga

[PHPStan extensions and rules](https://github.com/phpstan/phpstan) for [Silverstripe CMS](https://github.com/silverstripe).

## Features ✨

Here are some of the nice features this extension provides:

- Recognition that configuration properties are always [read and written](https://phpstan.org/developing-extensions/always-read-written-properties).
- Correct return type for `SilverStripe\Config\Collections\ConfigCollectionInterface::get()`.
- Correct return type for `SilverStripe\Core\Config\Config_ForClass::get()`.
- Resolution of `SilverStripe\Core\Extensible` magic methods and properties.
- Type specification for `SilverStripe\Core\Extensible::hasExtension()` and `SilverStripe\Core\Extensible::hasMethod()` methods.
- Correct return types for `SilverStripe\Core\Extension::$owner` and `SilverStripe\Core\Extension::getOwner()`.
- Correct return types for `SilverStripe\Core\Injector\Injector::get()` and `SilverStripe\Core\Injector\Injector::create()`.
- Correct return type for `SilverStripe\ORM\DataObject::dbObject()`.
- Type specification for `SilverStripe\Model\ModelData::hasField()` method.
- Type specification for `SilverStripe\View\ViewableData::hasField()` method.
- Various correct return types for commonly used Silverstripe modules.
- [Customisable rules to help make your application safer](docs/rules_overview.md).

## Installation 👷‍♀️

Install via composer.

```sh
composer require --dev cambis/silverstan
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```neon
includes:
    - vendor/cambis/silverstan/extension.neon
```

</details>


### Silverstripe 5.2 or greater is recommended

While this extension is not tied to a specific Silverstripe version it is recommended that you are on at least Silverstripe 5.2.

Silverstripe 5.2 introduces [generic typehints](https://docs.silverstripe.org/en/5/changelogs/beta/5.2.0-beta1/#generics). These changes allow the module to infer the types of objects without relying on an extension.

To make the best use of this module, make sure that your classes are correctly annotated using a combination of generics, and property/method annotations.

## Rules 🚨
Silverstan provides a set of customisable rules that can help make your application safer.

Each rule can be enabled/disabled individually using the configuration options, please refer to the [rules overview](docs/rules_overview.md) for the available options.

## SilverStripe\Dev\TestOnly 👨‍🔬
Complex analysis of `SilverStripe\Dev\TestOnly` classes is disabled by default. This is because these classes often contain dependencies that aren't provided by Silverstripe.

To enable complex analysis of these classes, please check the following option in your configuration file:
```yml
parameters:
    silverstan:
        includeTestOnly: true
```

If PHPStan complains about missing classes, be sure to add the corresponding package to your dev dependencies.

## SilverStripe\Core\Extensible 🧑‍🔬

### Solving magic methods and properties
Silverstan provides support for magic `SilverStripe\Core\Extensible` methods and properties.

Silverstan will attempt to resolve magic methods/properties by searching for existing annotations in the class ancestry first. If no annotation is found it will access the configuration API in order to resolve the magic method/property.

Using annotations is preferred, as they can often provide more information, have stricter types, and reduce the number of calls to the configuration API.

You can use [Silverstripe Rector](https://github.com/Cambis/silverstripe-rector) to create the annotations for you.

### Solving SilverStripe\Core\Extensible::hasExtension() and SilverStripe\Core\Extensible::hasMethod()

Silverstan provides type specifying extensions for these cases. However, these extensions can only be applied on a per class basis.

The default configuration applies these extensions to `SilverStripe\View\ViewableData` and `SilverStripe\Model\ModelData` only. If you wish to add them to other `SilverStripe\Core\Extensible` classes that aren't subclasses of the former you can use the following configuration:

```yml
services:
    -
        # Solves `Foo::hasExtension()`
        class: Cambis\Silverstan\Extension\Type\ExtensibleHasExtensionTypeSpecifyingExtension
        tags: [phpstan.typeSpecifier.methodTypeSpecifyingExtension]
        arguments:
            className: 'Foo'
    -
        # Solves `Foo::hasMethod()`
        class: Cambis\Silverstan\Extension\Type\ExtensibleHasMethodTypeSpecifyingExtension
        tags: [phpstan.typeSpecifier.methodTypeSpecifyingExtension]
        arguments:
            className: 'Foo'
```

### Solving SilverStripe\Core\Extensible::has_extension()

> [!WARNING]
> Silverstan does not support type specification for `SilverStripe\Core\Extensible::has_extension()`. If you use this method in your codebase, consider using one of the following examples to help solve errors that may be reported by PHPStan.

In the example below, we are adding a typehint to inform PHPStan of the expected type.
```diff
if (\SilverStripe\View\ViewableData::has_extension(Foo::class, FooExtension::class)) {
+  /** @var Foo&FooExtension $foo */
  $foo = Foo::create();
}
```

In the example below, we are changing the calls to use the dynamic `SilverStripe\Core\Extensible::hasExtension()` method which is supported by Silverstan.
```diff
$foo = Foo::create();

- if ($foo->has_extension(FooExtension::class)) {
+ if ($foo->hasExtension(FooExtension::class)) {
  // ...
}

- if ($foo::has_extension(FooExtension::class)) {
+ if ($foo->hasExtension(FooExtension::class)) {
  // ...
}
```

## SilverStripe\Core\Config\Config_ForClass 👩‍🔬

> [!WARNING]
> Silverstan cannot resolve the type of a property fetch on `SilverStripe\Core\Config\Config_ForClass`, use `SilverStripe\Core\Config\Config_ForClass::get()` instead. [See the rules overview](docs/rules_overview.md#disallowpropertyfetchonconfigforclassrule).
