/** @type {import('lint-staged').Config} */
module.exports = {
  '*.php': (filenames) => [
    `php vendor/bin/parallel-lint src tests --colors --blame ${filenames.join(' ')}`,
    `php vendor/bin/rector process --dry-run --ansi ${filenames.join(' ')}`,
    `php vendor/bin/ecs check --fix --ansi ${filenames.join(' ')}`,
    'php vendor/bin/phpstan analyse --ansi --memory-limit=-1',
  ],
  '!(e2e/*/composer.json)composer.json': [
    'composer normalize --ansi',
  ],
};
