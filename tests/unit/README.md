# Unit Tests for Joomla 4.0

This folder contains the unit tests for the Joomla CMS. The tests are run with phpunit and the actual tests.

## Folder Structure

* `core` - Several test support classes (not used currently)
* `suites` - The actual test classes
* `tmp` - A temporary directory used for filesystem operations in the test suite
* `bootstrap.php` - The testing bootstrap called when PHPUnit is run
* `phpunit.xml.dist` - The configuration for PHPUnit.

## Testsuites

There are currently several testsuites defined in the `phpunit.xml.dist`. You can run these separately from each other by adding `--testsuite <name>` on the command line. All testsuites prefixed with `libraries-` are related to the `/libraries` folder and should be considered legacy, except for the `libraries-4.0` testsuite. All tests from the libraries folder should be migrated to the `/libraries/src` folder.

## How to run the tests
When you are checking out the current development branch of 4.0 and run `composer install`, your system is automatically set up to run the tests. The steps thus are the following:

1. Checkout the current Joomla 4.0 development branch from Github. (https://github.com/joomla/joomla-cms.git Branch `4.0-dev`)
2. Run `composer install` in the root of your checkout.
3. Run `libraries/vendor/bin/phpunit --configuration ./tests/unit/phpunit.xml.dist` in the root of your checkout. This will execute all unit tests. On Windows machines you have to replace the slashes in the path accordingly.