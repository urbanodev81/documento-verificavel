#!/usr/bin/env bash
# Roda a suíte duas vezes: SQLite (rápido) e Postgres descartável (o banco de
# produção — o índice único e o tipo das colunas valem lá). Precisa só de Docker.
set -euo pipefail
cd "$(dirname "$0")/.."
U="$(id -u):$(id -g)"

docker run --rm -u "$U" -e COMPOSER_HOME=/tmp/c -v "$PWD":/app -w /app composer:2 \
  sh -c '[ -d vendor ] || composer install -q --no-interaction; vendor/bin/phpunit'

docker network create documento-teste >/dev/null
trap 'docker rm -f documento-pg >/dev/null 2>&1; docker network rm documento-teste >/dev/null 2>&1' EXIT
docker run -d --rm --name documento-pg --network documento-teste \
  -e POSTGRES_PASSWORD=teste -e POSTGRES_DB=documento postgres:16-alpine >/dev/null
until docker exec documento-pg pg_isready -U postgres -q; do sleep 1; done

# Imagem com pdo_pgsql e PHP 8.4 (o de produção).
docker run --rm --network documento-teste -u "$U" -e DB_HOST=documento-pg -e DB_PASSWORD=teste \
  -v "$PWD":/app -w /app --entrypoint php "${IMAGEM_PHP:-sail-8.4/app:latest}" \
  vendor/bin/phpunit -c tests/phpunit.pgsql.xml
