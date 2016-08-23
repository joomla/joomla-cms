#!/bin/bash
# Script for preparing the unit tests in Joomla!

# Path to the Joomla! installation
BASE="$1"

# Abort travis execution if setup fails
set -e

# Disable xdebug
phpenv config-rm xdebug.ini

# Make sure all dev dependencies are installed
composer install
