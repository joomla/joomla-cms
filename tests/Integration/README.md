# Integration Tests for Joomla 4.0

This folder contains the integration tests for the Joomla CMS. The tests are run with phpunit and the actual tests.

## How to run the tests

When you are checking out the current development branch of 4.0 and run `composer install`, your system is automatically set up to run the tests. The steps thus are the following:

1. Checkout the current Joomla 4.0 development branch from Github. (https://github.com/joomla/joomla-cms.git Branch `4.0-dev`)
2. Run `composer install` in the root of your checkout.
3. Copy `./phpunit.xml.dist` to `./phpunit.xml`. Edit configuration file `./phpunit.xml`. Within the `<php>` adapt 
`JTEST_DB_ENGINE`, `JTEST_DB_HOST`, `JTEST_DB_NAME`, `JTEST_DB_USER`, and `JTEST_DB_PASSWORD`
to your local environment.
```
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/Unit/bootstrap.php" colors="false">
	<testsuites>
		<testsuite name="Unit">
			<directory suffix="Test.php">./tests/Unit/Libraries</directory>
		</testsuite>
		<testsuite name="Integration">
			<directory suffix="Test.php">./tests/Integration/Libraries</directory>
		</testsuite>
	</testsuites>
	<php>
		<const name="JTEST_DB_ENGINE" value="mysqli" />
		<const name="JTEST_DB_HOST" value="localhost" />
		<const name="JTEST_DB_NAME" value="joomla_db" />
		<const name="JTEST_DB_USER" value="Your DB user" />
		<const name="JTEST_DB_PASSWORD" value="Your Password" />
	</php>
</phpunit>
```
4. Run `./libraries/vendor/bin/phpunit --testsuite Integration`. 
You should now see on the command line something like this:

```
$ ./libraries/vendor/bin/phpunit --testsuite Integration
PHPUnit 8.3.4 by Sebastian Bergmann and contributors.

........                                                            8 / 8 (100%)

Time: 155 ms, Memory: 10.00 MB

OK (8 tests, 33 assertions)
```
