#!/bin/zsh
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
CONF_PATH="$ROOT_DIR/php84-fpm.conf"
PHP_FPM_BIN="/usr/local/opt/php@8.4/sbin/php-fpm"
LISTEN_PORT="9074"

if [[ ! -x "$PHP_FPM_BIN" ]]; then
  echo "Missing PHP-FPM binary at $PHP_FPM_BIN" >&2
  exit 1
fi

if lsof -nP -iTCP:"$LISTEN_PORT" -sTCP:LISTEN >/dev/null 2>&1; then
  echo "PHP-FPM already listening on 127.0.0.1:$LISTEN_PORT"
  exit 0
fi

"$PHP_FPM_BIN" -t -y "$CONF_PATH"
"$PHP_FPM_BIN" -D -y "$CONF_PATH"

sleep 1

if lsof -nP -iTCP:"$LISTEN_PORT" -sTCP:LISTEN >/dev/null 2>&1; then
  echo "Started PHP-FPM on 127.0.0.1:$LISTEN_PORT"
  exit 0
fi

echo "PHP-FPM did not start listening on 127.0.0.1:$LISTEN_PORT" >&2
exit 1
