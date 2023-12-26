/** @type {import('lint-staged').Config} */
module.exports = {
  '*.php': [
    'php vendor/bin/ecs check --fix --ansi',
    'php vendor/bin/phpstan analyse --ansi --memory-limit=-1',
  ],
};
