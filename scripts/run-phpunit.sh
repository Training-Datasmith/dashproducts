#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
COMPOSER_PHAR="${COMPOSER_PHAR:-${ROOT}/composer-2.2.24.phar}"

if [[ -n "${PHP_BIN:-}" ]]; then
  :
elif command -v php5.6 >/dev/null 2>&1; then
  PHP_BIN="php5.6"
elif command -v php >/dev/null 2>&1; then
  PHP_BIN="php"
else
  echo "No PHP binary found (set PHP_BIN), or run the suite in Dockerfile.tests." >&2
  exit 1
fi

if [[ ! -f "${COMPOSER_PHAR}" ]]; then
  curl -fsSL https://getcomposer.org/download/2.2.24/composer.phar -o "${COMPOSER_PHAR}"
fi

"${PHP_BIN}" "${COMPOSER_PHAR}" install --no-interaction --no-progress --working-dir="${ROOT}/tests"
"${PHP_BIN}" "${ROOT}/vendor-phpunit/bin/phpunit" --configuration "${ROOT}/phpunit.xml.dist"
