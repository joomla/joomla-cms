#!/bin/bash

set -e

echo "--- Starting Post-Creation Setup ---"

# Configuration variables
DB_NAME="test_joomla"
DB_USER="joomla_ut"
DB_PASS="joomla_ut"
ADMIN_USER="ci-admin"
ADMIN_REAL_NAME="john doe"
ADMIN_PASS="joomla-17082005"
ADMIN_EMAIL="admin@example.org"
WORKSPACE_ROOT="/workspaces/joomla-cms"
JOOMLA_ROOT="/var/www/html"

git config --global --add safe.directory $WORKSPACE_ROOT

# --- 1. Wait for MariaDB ---
echo "--> Waiting for MariaDB..."
while ! mysqladmin ping -h"mysql" --silent; do
    sleep 1
done

# --- 2. Install Dependencies ---
echo "--> Installing dependencies..."
composer install
npm install

# --- 3. Install Joomla ---
echo "--> Installing Joomla..."
rm -f $WORKSPACE_ROOT/index.html
rm -f $WORKSPACE_ROOT/configuration.php
cd $WORKSPACE_ROOT

php installation/joomla.php install \
    --site-name="Joomla CMS Test" \
    --admin-user="$ADMIN_REAL_NAME" \
    --admin-username="$ADMIN_USER" \
    --admin-password="$ADMIN_PASS" \
    --admin-email="$ADMIN_EMAIL" \
    --db-type="mysqli" \
    --db-host="mysql" \
    --db-name="$DB_NAME" \
    --db-user="$DB_USER" \
    --db-pass="$DB_PASS" \
    --db-prefix="mysql_" \
    --db-encryption="0" \
    --public-folder=""

# --- 4. Configure Joomla ---
echo "--> Configuring Joomla..."
php cli/joomla.php config:set debug=true error_reporting=maximum

# --- 5. Download and prepare phpMyAdmin ---
PMA_ROOT="/var/www/html/phpmyadmin"
echo "--> Downloading phpMyAdmin into $PMA_ROOT..."
PMA_VERSION=5.2.2
mkdir -p $PMA_ROOT
curl -o /tmp/phpmyadmin.tar.gz https://files.phpmyadmin.net/phpMyAdmin/${PMA_VERSION}/phpMyAdmin-${PMA_VERSION}-all-languages.tar.gz
tar xf /tmp/phpmyadmin.tar.gz --strip-components=1 -C $PMA_ROOT
rm /tmp/phpmyadmin.tar.gz
cp $PMA_ROOT/config.sample.inc.php $PMA_ROOT/config.inc.php
sed -i "/\['AllowNoPassword'\] = false/a \$cfg['Servers'][\$i]['host'] = 'mysql';" $PMA_ROOT/config.inc.php

# --- 6. Codespaces Fix ---
echo "--> Applying Codespaces fix..."

cat > $WORKSPACE_ROOT/fix.php << 'EOF'
<?php
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost:80') {
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
}
EOF

# Include fix in both entry points
cp $WORKSPACE_ROOT/fix.php $WORKSPACE_ROOT/administrator/fix.php
sed -i '2i require_once __DIR__ . "/fix.php";' $WORKSPACE_ROOT/index.php
sed -i '2i require_once __DIR__ . "/../fix.php";' $WORKSPACE_ROOT/administrator/index.php


# --- 7. Finalize and setup Cypress ---
echo "--> Finalizing and setting up Cypress..."
# git update-index --assume-unchanged ./node_modules/.bin/cypress
chmod +x ./node_modules/.bin/cypress
chown -R www-data:www-data $JOOMLA_ROOT
npx cypress install
cp cypress.config.dist.mjs cypress.config.mjs
chown -R www-data:www-data /workspaces/joomla-cms
chmod -R a+rx /workspaces/joomla-cms
echo '<Directory /workspaces/joomla-cms>
    AllowOverride All
    Require all granted
</Directory>' | sudo tee -a /etc/apache2/apache2.conf
service apache2 restart

# Save details

DETAILS_FILE="${WORKSPACE_ROOT}/codespace-details.txt"
{
    echo ""
    echo "---"
    echo "✅ Setup complete! Your environment is ready."
    echo ""
    echo "This information has been saved to codespace-details.txt"
    echo ""
    echo "Joomla Admin Login:"
    echo "  URL: Open the 'Web Server' port and add /administrator to the end."
    echo "  Username: $ADMIN_USER"
    echo "  Password: $ADMIN_PASS"
    echo ""
    echo "phpMyAdmin Login:"
    echo "  URL: Open the 'Web Server' port and add /phpmyadmin to the end."
    echo "  Username: joomla_ut"
    echo "  Password: joomla_ut"
    echo ""
    echo "To use cypress testing"
    echo "  Open 'Cypress GUI' port."
    echo "  Run Interactive Cypress using 'npx cypress open' and you should see cypress on 'Cypress GUI' port page."
    echo "  You can also run Headless tests using 'npx cypress run'"
    echo "---"
} | tee "$DETAILS_FILE"
