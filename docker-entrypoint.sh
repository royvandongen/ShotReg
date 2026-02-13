#!/bin/sh
# Don't use set -e here, we want to handle errors explicitly

echo "Starting ShotReg..."

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

# Check if migrations table exists (first boot detection)
MIGRATIONS_TABLE_EXISTS=$(php -r '
    $host = getenv("database_default_hostname") ?: "localhost";
    $port = (int) (getenv("database_default_port") ?: 3306);
    $db = getenv("database_default_database") ?: "";
    $user = getenv("database_default_username") ?: "";
    $pass = getenv("database_default_password") ?: "";
    $mysqli = @new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_error) { echo "error"; exit; }
    $result = $mysqli->query("SHOW TABLES LIKE \"migrations\"");
    echo ($result && $result->num_rows > 0) ? "yes" : "no";
' 2>/dev/null)

NEED_MIGRATIONS=false

if [ "$MIGRATIONS_TABLE_EXISTS" = "no" ]; then
    echo "First boot detected (no migrations table yet), running all migrations..."
    NEED_MIGRATIONS=true
elif [ "$MIGRATIONS_TABLE_EXISTS" = "yes" ]; then
    echo "Checking migration status..."
    MIGRATION_STATUS_OUTPUT=$(php spark migrate:status 2>&1)
    if echo "$MIGRATION_STATUS_OUTPUT" | grep -q '| --- |'; then
        echo "Found pending migrations, running..."
        NEED_MIGRATIONS=true
    else
        echo "All migrations are up to date."
    fi
else
    echo "Could not check migrations table, running migrations to be safe..."
    NEED_MIGRATIONS=true
fi

if [ "$NEED_MIGRATIONS" = "true" ]; then
    MIGRATION_OUTPUT=$(php spark migrate --all 2>&1)
    MIGRATION_EXIT=$?

    if [ $MIGRATION_EXIT -eq 0 ]; then
        echo "Migrations completed successfully."
        echo "$MIGRATION_OUTPUT"
    else
        echo "Migration failed (exit code: $MIGRATION_EXIT)"
        echo "$MIGRATION_OUTPUT"
        exit 1
    fi
fi

# Create admin user if environment variables are set (only after successful migrations)
if [ -n "$ADMIN_USERNAME" ] && [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
    if [ "$NEED_MIGRATIONS" = "true" ] && [ $MIGRATION_EXIT -ne 0 ]; then
        echo "Skipping admin creation due to migration failure."
    else
        echo "Creating admin user..."
        php spark admin:create
    fi
fi

# Ensure writable permissions
chown -R www-data:www-data /var/www/html/writable

# Execute the main command (apache2-foreground)
echo "Starting web server..."
exec "$@"
