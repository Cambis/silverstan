/** @type {import('lint-staged').Config} */
module.exports = {
  '*.php': [
    'php vendor/bin/parallel-lint --colors --blame',
    'php vendor/bin/rector process --ansi',
    'php vendor/bin/ecs check --fix --ansi',
  ],
  '!(e2e/*/composer.json)composer.json': [
    'composer normalize --ansi'
  ],
};
