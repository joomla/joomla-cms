#!/bin/bash
# Script for preparing the system tests in Joomla!

# Start apache
service apache2 restart

# Start Xvfb
export DISPLAY=:0
Xvfb -screen 0 1024x768x24 -ac +extension GLX +render -noreset &
sleep 1 # give xvfb some time to start

# Start Fluxbox
fluxbox &
sleep 3 # give fluxbox some time to start

google-chrome

# Move folder to /tests
ln -s $(pwd) /tests/www

# Composer install in tests folder
cd tests/codeception
composer install
cd ../..

# Run the tests
cp RoboFile.dist.ini RoboFile.ini
cp tests/codeception/acceptance.suite.dist.yml tests/codeception/acceptance.suite.yml

# Run tests
./tests/codeception/vendor/bin/robo run:tests
