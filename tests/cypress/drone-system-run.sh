#!/usr/bin/env bash
set -e
JOOMLA_BASE=$1
TEST_GROUP=$2
DB_ENGINE=$3
DB_HOST=$4

echo "[RUNNER] Prepare test environment"

# Switch to Joomla base directory
cd $JOOMLA_BASE

echo "[RUNNER] Copy files to test installation"
rsync -a --exclude-from=tests/cypress/exclude.txt $JOOMLA_BASE/ /tests/www/$TEST_GROUP/
chown -R www-data /tests/www/$TEST_GROUP/

# Is needed for media manager tests
chmod -R 777 /tests/www/$TEST_GROUP/images

echo "[RUNNER] Start Apache"
apache2ctl -D FOREGROUND &

echo "[RUNNER] Run cypress installations tests"
chmod +rwx /root

# Run first the installation test
npx cypress run --browser=firefox --e2e --env db_type=$DB_ENGINE,db_host=$DB_HOST,db_password=joomla_ut,db_prefix="${TEST_GROUP}_" --config baseUrl=http://localhost/$TEST_GROUP,screenshotsFolder=$JOOMLA_BASE/tests/cypress/output/screenshots --spec 'tests/cypress/integration/install/*.cy.js'

echo "[RUNNER] Run cypress CMS tests"

# If you have found this line failing on OSX you need to brew install gnu-sed like we mentioned in the cypress readme!
# This replaces the site secret in configuration.php so we can guarantee a consistent API token for our super user.
sed -i "/\$secret/c\	public \$secret = 'tEstValue';" /tests/www/$TEST_GROUP/configuration.php

npx cypress run --browser=firefox --e2e --env db_type=$DB_ENGINE,db_host=$DB_HOST,db_password=joomla_ut,db_prefix="${TEST_GROUP}_" --config baseUrl=http://localhost/$TEST_GROUP,screenshotsFolder=$JOOMLA_BASE/tests/cypress/output/screenshots --spec 'tests/cypress/integration/administrator/**/*.cy.js,tests/cypress/integration/site/**/*.cy.js,tests/cypress/integration/api/**/*.cy.js'
