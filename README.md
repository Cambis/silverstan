# Silverstan | Kaitiaki Ponga

This project contains extra strict and opinionated [PHPStan rules](https://github.com/phpstan/phpstan) for [Silverstripe CMS](https://github.com/silverstripe).

See the available [Silverstripe rules](docs/rules_overview.md).

## Prerequisites 🦺

```sh
silverstripe/framework ^5.2
silverstripe/cms ^5.2
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

Each rule can be enabled/disabled individually using the configuration options, please refer to the [rules overview](docs/rules_overview.md) for the available options.
