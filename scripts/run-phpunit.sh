#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_BIN="${PHP_BIN:-php5.6}"
COMPOSER_PHAR="${COMPOSER_PHAR:-${ROOT}/composer-2.2.24.phar}"

if ! command -v "${PHP_BIN}" >/dev/null 2>&1; then
  echo "PHP 5.6 binary not found (set PHP_BIN). On hosts with Docker, use Dockerfile.tests when overlay storage works." >&2
  exit 1
fi

if [[ ! -f "${COMPOSER_PHAR}" ]]; then
  curl -fsSL https://getcomposer.org/download/2.2.24/composer.phar -o "${COMPOSER_PHAR}"
fi

"${PHP_BIN}" "${COMPOSER_PHAR}" install --no-interaction --no-progress --working-dir="${ROOT}/tests"
"${PHP_BIN}" "${ROOT}/vendor-phpunit/bin/phpunit" --configuration "${ROOT}/phpunit.xml.dist"
