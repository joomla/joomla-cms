#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "--- Starting Joomla Core Post-Creation Setup ---"

# Configuration variables
DB_NAME="test_joomla"
DB_USER="joomla_ut"
DB_PASS="joomla_ut"
ADMIN_USER="ci-admin"
ADMIN_REAL_NAME="jane doe"
ADMIN_PASS="joomla-17082005"
ADMIN_EMAIL="admin@example.com"
JOOMLA_ROOT="/workspaces/joomla-cms"

# Allow git commands to run safely in the container
git config --global --add safe.directory $JOOMLA_ROOT

# --- 1. Wait for MariaDB Service ---
echo "--> Waiting for MariaDB to become available..."
while ! mysqladmin ping -h"mysql" --silent; do
    sleep 1
done
echo "✅ MariaDB is ready."

# --- 2. Install Core Dependencies ---
echo "--> Installing Composer and NPM dependencies..."
composer install
npm install
echo "✅ Dependencies installed."

# --- 3. Install Joomla from Repository Source ---
echo "--> Installing Joomla using the local repository source..."
rm -f configuration.php
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
    --db-prefix="jos_" \
    --db-encryption="0" \
    --public-folder=""
echo "✅ Joomla installed."

# --- 4. Configure Joomla for Development ---
echo "--> Applying development settings..."
# Enable debug mode and maximum error reporting for easier troubleshooting.
php cli/joomla.php config:set error_reporting=maximum
# Configure mail settings for Mailpit
php cli/joomla.php config:set mailer=smtp
php cli/joomla.php config:set smtphost=mailpit
php cli/joomla.php config:set smtpport=1025
php cli/joomla.php config:set smtpauth=0
php cli/joomla.php config:set smtpsecure=none
echo "✅ Development settings applied."

# --- 5. Install and Configure phpMyAdmin ---
PMA_ROOT="${JOOMLA_ROOT}/phpmyadmin"
echo "--> Downloading phpMyAdmin into $PMA_ROOT..."
PMA_VERSION=5.2.2
mkdir -p $PMA_ROOT
curl -o /tmp/phpmyadmin.tar.gz https://files.phpmyadmin.net/phpMyAdmin/${PMA_VERSION}/phpMyAdmin-${PMA_VERSION}-all-languages.tar.gz
tar xf /tmp/phpmyadmin.tar.gz --strip-components=1 -C $PMA_ROOT
rm /tmp/phpmyadmin.tar.gz
cp $PMA_ROOT/config.sample.inc.php $PMA_ROOT/config.inc.php
sed -i "/\['AllowNoPassword'\] = false/a \$cfg['Servers'][\$i]['host'] = 'mysql';" $PMA_ROOT/config.inc.php

# --- 6. Apply Codespaces Host Fix ---
# This ensures Joomla generates correct URLs when accessed through the forwarded port.
echo "--> Applying Codespaces URL fix..."
cat > "${JOOMLA_ROOT}/fix.php" << 'EOF'
<?php
// Fix for incorrect host when running behind the Codespaces reverse proxy.
if (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'localhost')) {
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
}
EOF

# Include fix in both entry points
sed -i '2i require_once __DIR__ . "/fix.php";' $JOOMLA_ROOT/index.php
sed -i '2i require_once __DIR__ . "/../fix.php";' $JOOMLA_ROOT/administrator/index.php

echo "--> Ignoring local changes..."
# For TRACKED files, tell Git to stop watching them for changes
git update-index --assume-unchanged "index.php"
git update-index --assume-unchanged "administrator/index.php"
git update-index --assume-unchanged "package-lock.json"
git update-index --assume-unchanged "tests/System/integration/install/Installation.cy.js"
git update-index --assume-unchanged "tests/System/support/commands/config.mjs"

# For NEW UNTRACKED files, add them to the local exclude file
echo "cypress.config.js" >> ".git/info/exclude"
echo "fix.php" >> ".git/info/exclude"
echo "phpmyadmin" >> ".git/info/exclude"
echo "codespace-details.txt" >> ".git/info/exclude"

# --- 7. Finalize Permissions and Testing Tools ---
echo "--> Setting up file permissions and Cypress..."
sed -i \
  -e "/\/\/ If exists, delete PHP configuration file to force a new installation/d" \
  -e "/cy.task('deleteRelativePath', 'configuration.php');/d" \
  -e "/cy.installJoomla(config);/d" \
  tests/System/integration/install/Installation.cy.js
sed -i "s/return cy.task('writeRelativeFile', { path: 'configuration.php', content });/return cy.task('writeRelativeFile', { path: 'configuration.php', content, mode: 0o775 });/" tests/System/support/commands/config.mjs

# Ensure Cypress is executable and owned by the web server user
chmod +x ./node_modules/.bin/cypress
cp cypress.config.dist.mjs cypress.config.js
npx cypress install
sed -i -e "s|baseUrl:.*|baseUrl: 'https://localhost',|" -e "s/db_host: 'localhost'/db_host: 'mysql'/g" -e "s/db_user: 'root'/db_user: 'joomla_ut'/g" -e "s/db_password: ''/db_password: 'joomla_ut'/g" cypress.config.js

# Restart Apache to apply all changes
echo '<Directory /workspaces/joomla-cms>
    AllowOverride All
    Require all granted
</Directory>' | sudo tee -a /etc/apache2/apache2.conf
service apache2 restart

# Set the group to www-data and enforce group permissions
echo "--> Applying final group ownership and permissions..."
chgrp -R www-data $JOOMLA_ROOT
chmod -R g+rws $JOOMLA_ROOT

echo "✅ Environment finalized."

# --- 8. Display Setup Details ---
# Save the details to a file for easy reference.
DETAILS_FILE="${JOOMLA_ROOT}/codespace-details.txt"
{
    echo ""
    echo "---"
    echo "🚀 Joomla Core development environment is ready! 🚀"
    echo ""
    echo "This information has been saved to codespace-details.txt"
    echo ""
    echo "Joomla Admin Login:"
    echo "  URL: Open the 'Ports' tab, find the 'Web Server' (443), and click the Globe icon. Then add /administrator"
    echo "  Username: $ADMIN_USER"
    echo "  Password: $ADMIN_PASS"
    echo ""
    echo "phpMyAdmin Login:"
    echo "  URL: Open the 'Web Server' port and add /phpmyadmin"
    echo "  Username: $DB_USER"
    echo "  Password: $DB_PASS"
    echo ""
    echo "Mailpit (Email Testing):"
    echo "  URL: Open the 'Ports' tab, find 'Mailpit Web UI' (8025), and click the Globe icon"
    echo "  All emails sent by Joomla will appear here for testing"
    echo ""
    echo "Cypress E2E Testing:"
    echo "  Run interactive tests: npx cypress open"
    echo "  Run headless tests:   npx cypress run"
    echo ""
    echo "Xdebug for PHP Debugging:"
    echo "  Xdebug is pre-configured on port 9003. Use the 'Run and Debug' panel in VS Code and select 'Listen for Xdebug'."
    echo "---"
} | tee "$DETAILS_FILE"
