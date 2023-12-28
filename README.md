# Silverstan | Kaitiaki Ponga

This project contains [PHPStan rules](hhttps://github.com/phpstan/phpstan) for [Silverstripe CMS](https://github.com/silverstripe).

See available [Silverstripe rules](docs/rules_overview.md).

## Prerequisites 🦺

```sh
silverstripe/framework ^5.0
silverstripe/cms ^5.0
```

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

TODO
