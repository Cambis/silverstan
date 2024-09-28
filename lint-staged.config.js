/** @type {import('lint-staged').Config} */
module.exports = {
  '*.php': [
    'php vendor/bin/parallel-lint --colors --blame',
    'php vendor/bin/ecs check --fix --ansi',
  ],
  'composer.json': [
    'composer normalize --ansi'
  ],
};
