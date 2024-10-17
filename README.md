# Silverstan | Kaitiaki Ponga

[PHPStan extensions and rules](https://github.com/phpstan/phpstan) for [Silverstripe CMS](https://github.com/silverstripe).

## Features ✨

Here are some of the nice features this extension provides:

- Support for read-write configuration properties.
- Correct `SilverStripe\Core\Extension::$owner` and `SilverStripe\Core\Extension::getOwner()` return types.
- `SilverStripe\Core\Extensible` magic methods and properties.
- `SilverStripe\Config\Collections\ConfigCollectionInterface::get()` and `SilverStripe\Core\Config\Config_ForClass::get()` return types.
- `SilverStripe\Core\Extensible::hasExtension()` and `SilverStripe\Core\Extensible::hasMethod()` type specification.
- Various correct return types for commonly used Silverstripe modules.
- [Configurable rules to help make your application safer](docs/rules_overview.md).

## Prerequisites 🦺

```sh
silverstripe/framework ^5.2
```

### Why Silverstripe 5.2?

Silverstripe 5.2 introduces [generic typehints](https://docs.silverstripe.org/en/5/changelogs/beta/5.2.0-beta1/#generics). These changes allow the module to infer the types of objects without relying on an extension.

To make the best use of this module, make sure that your classes are correctly annotated using a combination of generics, and property/method annotations.

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

## Configuration 🚧

### Rules
Each rule can be enabled/disabled individually using the configuration options, please refer to the [rules overview](docs/rules_overview.md) for the available options.

### Analysing `SilverStripe\Dev\TestOnly` classes
Complex analysis of `SilverStripe\Dev\TestOnly` classes is disabled by default. This is because these classes often contain dependencies that aren't provided by Silverstripe.

To enable complex analysis of these classes, please check the following option in your configuration file:
```yml
parameters:
    silverstan:
        includeTestOnly: true
```

If PHPStan complains about missing classes, be sure to add the corresponding package to your dev dependencies.
