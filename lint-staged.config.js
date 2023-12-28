/** @type {import('lint-staged').Config} */
module.exports = {
  '*.php': [
    'php vendor/bin/parallel-lint --colors --blame',
    'php vendor/bin/ecs check --fix --ansi',
    'php vendor/bin/phpstan analyse --ansi --memory-limit=-1',
  ],
  'composer.json': [
    'composer normalize --ansi'
  ],
};
