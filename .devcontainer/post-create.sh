#!/bin/bash

sudo chmod -R 777 /workspaces/joomla-cms

# Auto-detect PR branch from GitHub environment variables
if [ -n "$GITHUB_HEAD_REF" ]; then
    PR_BRANCH="$GITHUB_HEAD_REF"  # For PRs
else
    PR_BRANCH="$GITHUB_REF_NAME"   # For direct branch pushes
fi
# Clone or update repository
if [ ! -d "/workspaces/joomla-cms" ]; then
    echo "Cloning PR branch: $PR_BRANCH"
    git clone --branch "$PR_BRANCH" "https://github.com/$GITHUB_REPOSITORY.git" /workspaces/joomla-cms
    cd /workspaces/joomla-cms || exit
    
    # Set upstream if working from a fork
    if [[ "$GITHUB_REPOSITORY" != "joomla/joomla-cms" ]]; then
        git remote add upstream "https://github.com/joomla/joomla-cms.git"
    fi
else
    cd /workspaces/joomla-cms || exit
    git fetch origin
    git checkout "$PR_BRANCH"
fi

# Install PHP dependencies
cd /workspaces/joomla-cms
composer install --ignore-platform-req=ext-ldap
npm ci

# Set up Apache to serve Joomla from the workspace
sudo rm -rf /var/www/html
sudo ln -s /workspaces/joomla-cms /var/www/html

# Start Apache
sudo service apache2 start

# Start MySQL and set up Joomla database
# Start MariaDB service
sudo service mariadb start

# Configure database
sudo mysql -e "CREATE DATABASE IF NOT EXISTS joomla_db;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'joomla_user'@'%' IDENTIFIED BY 'joomla_pass';"
sudo mysql -e "GRANT ALL PRIVILEGES ON joomla_db.* TO 'joomla_user'@'%';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Secure MariaDB (optional)
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '';"

# remove configuration file
sudo rm -f configuration.php
# install joomla
php installation/joomla.php install --verbose --site-name="Joomla CMS test" --admin-email=admin@example.org --admin-username=ci-admin --admin-user="jane doe" --admin-password=joomla-17082005 --db-type=mysqli --db-host=127.0.0.1 --db-name=joomla_db --db-pass=joomla_pass --db-user=joomla_user --db-encryption=0 --db-prefix=jos_ <<< ""
echo "Joomla CMS is ready at http://localhost:8080"
