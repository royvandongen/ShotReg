#!/bin/bash
set -e

# Wait for MySQL to be ready
if [ -n "$database_default_hostname" ]; then
    echo "Waiting for MySQL at ${database_default_hostname}:${database_default_port:-3306}..."
    for i in $(seq 1 30); do
        if mysqladmin ping -h "$database_default_hostname" -P "${database_default_port:-3306}" -u "${database_default_username}" -p"${database_default_password}" --silent 2>/dev/null; then
            echo "MySQL is ready."
            break
        fi
        echo "Attempt $i/30 - MySQL not ready, waiting..."
        sleep 2
    done
fi

# Run migrations
echo "Running database migrations..."
php spark migrate --all

# Create admin user if environment variables are set
if [ -n "$ADMIN_USERNAME" ] && [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
    echo "Creating admin user..."
    php spark admin:create
fi

# Ensure writable permissions
chown -R www-data:www-data /var/www/html/writable

# Execute the main command
exec "$@"
