#!/bin/sh
# Don't use set -e here, we want to handle errors explicitly

echo "Starting Shotr..."

# Map friendly env var names to CI4 format
if [ -n "$APP_BASE_URL" ] && [ -z "$app_baseURL" ]; then
    export app_baseURL="$APP_BASE_URL"
fi

# Skip migrations if SKIP_MIGRATIONS is set
if [ "$SKIP_MIGRATIONS" = "true" ]; then
    echo "Skipping migrations (SKIP_MIGRATIONS=true)"
    chown -R www-data:www-data /var/www/html/writable
    exec "$@"
fi

# Wait for database to be available
if [ -n "$database_default_hostname" ]; then
    echo "Waiting for database at ${database_default_hostname}:${database_default_port:-3306}..."
    MAX_RETRIES=30
    RETRY_COUNT=0

    until php -r '
        $host = getenv("database_default_hostname") ?: "localhost";
        $port = (int) (getenv("database_default_port") ?: 3306);
        $db = getenv("database_default_database") ?: "";
        $user = getenv("database_default_username") ?: "";
        $pass = getenv("database_default_password") ?: "";
        $mysqli = @new mysqli($host, $user, $pass, $db, $port);
        if ($mysqli->connect_error) { exit(1); }
        $result = $mysqli->query("SELECT 1");
        if (! $result) { exit(1); }
        exit(0);
    ' 2>/dev/null; do
        RETRY_COUNT=$((RETRY_COUNT + 1))
        if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
            echo "Database connection timeout after $MAX_RETRIES attempts"
            exit 1
        fi
        echo "Database unavailable - attempt $RETRY_COUNT/$MAX_RETRIES"
        sleep 2
    done
    echo "Database is ready."
fi

# Run migrations (idempotent — does nothing if already up to date)
echo "Running migrations..."
MIGRATION_OUTPUT=$(php spark migrate --all 2>&1)
MIGRATION_EXIT=$?

if [ $MIGRATION_EXIT -eq 0 ]; then
    echo "$MIGRATION_OUTPUT"
else
    echo "Migration failed (exit code: $MIGRATION_EXIT)"
    echo "$MIGRATION_OUTPUT"
    exit 1
fi

# Create admin user if environment variables are set
if [ -n "$ADMIN_USERNAME" ] && [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
    echo "Creating admin user..."
    php spark admin:create
fi

# Ensure writable permissions
chown -R www-data:www-data /var/www/html/writable

# Execute the main command (apache2-foreground)
echo "Starting web server..."
exec "$@"
