#!/bin/bash
# Script for preparing the unit tests in Joomla!

# Path to the Joomla! installation
BASE="$1"

# Abort travis execution if setup fails
set -e

# Make sure all dev dependencies are installed
composer install

# Disable xdebug
phpenv config-rm xdebug.ini
