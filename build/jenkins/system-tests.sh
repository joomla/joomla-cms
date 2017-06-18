#!/bin/bash
# Script for preparing the system tests in Joomla!

# Path to the Joomla! installation
BASE="/opt/src"

# Start apache
service apache2 restart

# Start Xvfb
bash /etc/init.d/xvfb start
sleep 1 # give xvfb some time to start

# Start Fluxbox
fluxbox &
sleep 3 # give fluxbox some time to start

# Composer install in tests folder
cd tests/codeception
composer install
cd $BASE

# Run the tests
cp RoboFile.dist.ini RoboFile.ini
cp tests/codeception/acceptance.suite.dist.yml tests/codeception/acceptance.suite.yml