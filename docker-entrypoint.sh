#!/bin/sh
set -e

# Ensure the SQLite database file exists (it is gitignored)
touch database/database.sqlite

# Make storage writable
chmod -R 775 storage bootstrap/cache

# Run migrations and seed demo data on each cold start
# The seeder is guarded (skips if users already exist), so it is idempotent.
php artisan migrate --force --seed

# Start the server on Railway's PORT (default 8080)
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"