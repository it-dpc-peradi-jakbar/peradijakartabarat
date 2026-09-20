#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
    storage/logs bootstrap/cache storage/app
chmod -R ug+rwx storage bootstrap/cache || true

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Laravel reads .env into the HTTP workers; keep it in sync with Compose.
set_env() {
    if grep -q "^${1}=" .env; then
        sed -i "s|^${1}=.*|${1}=${2}|" .env
    else
        echo "${1}=${2}" >> .env
    fi
}
set_env DB_CONNECTION mysql
set_env DB_HOST mysql
set_env DB_PORT 3306
set_env DB_DATABASE peradi_jakbar
set_env DB_USERNAME peradi
set_env DB_PASSWORD peradi
set_env APP_URL http://localhost:8000
set_env PHP_CLI_SERVER_WORKERS 2
set_env FEATURE_LOGBOOK false

composer install --no-interaction --prefer-dist
if ! grep -Eq '^APP_KEY=.+' .env; then
    php artisan key:generate --force --no-interaction
fi

if [ ! -d node_modules ]; then
    npm ci
fi
if [ ! -f public/build/manifest.json ]; then
    npm run build
fi

echo "Waiting for MySQL..."
i=0
until php -r "
try {
  new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
  );
} catch (Throwable \$e) { fwrite(STDERR, \$e->getMessage()); exit(1); }
" 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -gt 60 ]; then
        echo "MySQL did not become ready."
        exit 1
    fi
    sleep 2
done

php artisan migrate --force --no-interaction
php artisan wilayah:import --no-interaction

if [ ! -f storage/app/.docker_seeded ]; then
    php artisan db:seed --force --no-interaction
    touch storage/app/.docker_seeded
fi

echo "App: http://localhost:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
