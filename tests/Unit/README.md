# Unit Tests for Joomla 4.x

This folder contains the unit tests for the Joomla CMS. The tests are run with phpunit and the actual tests.

## How to run the tests

When you are checking out the current development branch of 4.x and run `composer install`, your system is automatically set up to run the tests. The steps thus are the following:

1. Checkout the current Joomla 4.x development branch from Github. (https://github.com/joomla/joomla-cms.git)
2. Run `composer install` in the root of your checkout.
3. Run `./libraries/vendor/bin/phpunit --testsuite Unit`. The configuration file phpunit.xml.dist is used if phpunit.xml does not exist, but no edits are needed for the Unit tests.
