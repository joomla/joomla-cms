# Integration Tests for Joomla

This folder contains the integration tests for the Joomla CMS. The tests are run with phpunit and the actual tests.

## How to run the tests

When you are checking out the current development branch of Joomla and run `composer install`, your system is automatically set up to run the tests. The steps thus are the following:

1. Checkout the current Joomla development branch from Github. (https://github.com/joomla/joomla-cms.git)
2. Run `composer install` in the root of your checkout.
3. Copy `./phpunit.xml.dist` to `./phpunit.xml`. Edit configuration file `./phpunit.xml`. Within the `<php>` adapt the value of
`JTEST_DB_ENGINE` (mysqli or pgsql), `JTEST_DB_HOST`, `JTEST_DB_NAME`, `JTEST_DB_USER`, and `JTEST_DB_PASSWORD`
to your local environment.
```
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/Unit/bootstrap.php" colors="false">
	<testsuites>
		<testsuite name="Unit">
			<directory suffix="Test.php">./tests/Unit</directory>
		</testsuite>
		<testsuite name="Integration">
			<directory suffix="Test.php">./tests/Integration</directory>
		</testsuite>
	</testsuites>
	<php>
		<const name="JTEST_DB_ENGINE" value="mysqli" />
		<const name="JTEST_DB_HOST" value="localhost" />
		<const name="JTEST_DB_NAME" value="joomla_db" />
		<const name="JTEST_DB_USER" value="Your DB user" />
		<const name="JTEST_DB_PASSWORD" value="Your Password" />
		...
	</php>
</phpunit>
```
4. Run an openldap docker image and/or configure the ldap settings in `./phpunit.xml`.
   * If you set JTEST_LDAP_HOST to `localhost` (or change your hosts file so "openldap" points to the ip where the service listens for connections), the following command should give you a working configuration. If needed, replace `$(pwd)` with the path to where the docker service can access the Joomla root.
`docker run --rm --name openldap --env LDAP_ADMIN_USERNAME=admin --env LDAP_ADMIN_PASSWORD=adminpassword --env LDAP_USERS=customuser --env LDAP_PASSWORDS=custompassword --publish 1389:1389 --publish 1636:1636 --env LDAP_ENABLE_TLS=yes --env LDAP_TLS_CERT_FILE=/opt/bitnami/certs/openldap.crt --env LDAP_TLS_KEY_FILE=/opt/bitnami/certs/openldap.key --env LDAP_TLS_CA_FILE=/opt/bitnami/certs/CA.crt --env LDAP_CONFIG_ADMIN_ENABLED=yes --env LDAP_CONFIG_ADMIN_USERNAME=admin --env LDAP_CONFIG_ADMIN_PASSWORD=configpassword --env BITNAMI_DEBUG=true -v $(pwd)/tests/certs:/opt/bitnami/certs bitnami/openldap:latest`
   * If your ldap server supports "None", "STARTTLS" and "SSL/TLS" encryption and works with both "Bind and Search" and "Bind Directly as User" methods, you can use your own if you configure the `JTEST_LDAP_` directives in your `phpunit.xml` file according to your environment.
   * If you do not want to run the docker openldap image and don't have an LDAP server running, you can skip the LDAP tests if you set JTEST_LDAP_HOST to an empty value.
5. Run `./libraries/vendor/bin/phpunit --testsuite Integration`.

You should now see on the command line something like this:

```
$ ./libraries/vendor/bin/phpunit --testsuite Integration
PHPUnit 8.3.4 by Sebastian Bergmann and contributors.

........                                                            8 / 8 (100%)

Time: 155 ms, Memory: 10.00 MB

OK (8 tests, 33 assertions)
```

If you configured your environment for the integration tests, you can run integration and unit tests at once, using `./libraries/vendor/bin/phpunit`.
