# Unit Tests for Joomla 4.x

This folder contains the unit tests for the Joomla CMS. The tests are run with phpunit and the actual tests.

## How to run the tests

When you are checking out the current development branch of 4.x and run `composer install`, your system is automatically set up to run the tests. The steps thus are the following:

1. Checkout the current Joomla 4.x development branch from Github. (https://github.com/joomla/joomla-cms.git)
2. Run `composer install` in the root of your checkout.
3. Add the file `phpunit.xml` and update the values dependent on your environment. You can use the files `phpunit.xml.dist` and/or `phpunit-pgsql.xml.dist` as an example.

```
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/Unit/bootstrap.php" colors="false">
...
	<php>
		...
		<const name="JTEST_DB_NAME" value="YOUR_DB_NAME" />
		<const name="JTEST_DB_USER" value="YOUR_USER" />
		<const name="JTEST_DB_PASSWORD" value="YOUR_PASSWORD" />
	</php>
</phpunit>
```
4. Run `libraries/vendor/bin/phpunit`
