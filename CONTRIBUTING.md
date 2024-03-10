# Contributing 🤩

Kia ora! Would you like to contribute? That's awesome, thank you so much for your interest in this project!

Before you go committing your amazing contribution please read the following guidelines.

## Getting started 👶

Here are some things to know before you start coding.

We use the following dependencies for development:

- php 8.3
- composer
- node >=18

We use a number of quality of life tools to streamline development, install them via the command line.

```sh
composer install
yarn install
```

## Coding standards 👮‍♂️

To keep the codebase tidy, use the following script to clean each commit.

```sh
composer lint
```

## Commit standards 👮‍♀️

This project uses the [gitmoji config for commitlint](https://www.npmjs.com/package/commitlint-config-gitmoji#structure).

Each commit should adhere to the following structure.

```sh
:gitmoji: type(scope?): subject
body?
footer?
```

## Testing 🧑‍🔬

Be sure to run the test suite regularly. New tests should be added for new features.

```sh
vendor/bin/phpunit
```

## Making a pull request ✨

This project uses [changesets](https://github.com/changesets/changesets). This tool helps us to streamline our changelog.

When making a pull request, be sure to add a changeset if there has been a change to the project.

```sh
npx changeset
```
