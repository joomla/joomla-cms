### Configuring Code Analysis Tools

#### Running Unit Tests

Before code gets pulled into the master repository, the unit testing
suite is run to ensure that the change candidates do not leave trunk in
an unstable state (i.e. all tests should pass at all times). In order to
make the process of getting your code accepted it is helpful to run
these tests locally to prevent any unexpected surprises.

The Joomla Platform unit tests are developed for use with PHPUnit 3.6,
which is the latest stable version as of August 2011. Please see the
[PHPUnit Manual](http://www.phpunit.de/manual/3.6/en/installation.html)
for information on installing PHPUnit on your system.

##### Configuring Your Environment: The Database

Standard unit tests run against a
[Sqlite](http://www.sqlite.org/quickstart.html) in memory database for
ease of setup and performance. Other than [installing
Sqlite](http://www.sqlite.org/quickstart.html) no manual intervention or
set up is required. The database is built at runtime and deleted when
finished.

To run the specific database tests:

-   Create your database and use the appropriate database-specific DDL
    located in tests/suites/database/stubs to create the database tables
    required.

-   In the root directory, copy the file named phpunit.xml.dist, leaving
    it in the same folder and naming it phpunit.xml.

-   Uncomment the php block and include the const line(s) related to the
    database(s) you will be testing.

-   Set up the database configuration values for your specific
    environment.

##### Configuring Your Environment: The JHttpTransport Test Stubs

There is a special stub that is required for testing the JHttp
transports so that actual web requests can be simulated and assertions
can be made about the results. To set these up, you'll need to do the
following:

-   In the root directory, copy the file named phpunit.xml.dist, leaving
    it in the same folder and naming it phpunit.xml.

-   Uncomment the php block and include the "JTEST\_HTTP\_STUB" const.

-   The default file path for the const assumes that you have checked
    out the Joomla Platform to the web root of your test environment
    inside a folder named "joomla-platform". If this is not the case,
    you can change the path to suit your environment and, if need be,
    copy the file from its default location to be available within your
    web environment.

##### Running the Tests

You can run the tests by going to the platform root directory and
executing `phpunit`

Alternatively, if you have Ant installed on your system, you may run the
unit tests by going to the platform root directory and executing
`ant phpunit` to execute the tests on classes located under the
libraries/joomla directory or executing `ant phpunit-legacy` to execute
the tests on classes located under the libraries/legacy directory.

#### Coding Standards Analysis

In order to improve the consistency and readability of the source code,
we run a coding style analysis tool everytime changes are pushed in the
repo. For new contributions we are going to be enforcing coding
standards to ensure that the coding style in the source code is
consistent. Ensuring that your code meets these standards will make the
process of code contribution smoother.

The Joomla Platform sniffer rules are written to be used with a tool
called PHP\_CodeSniffer. Please see the [PHP\_CodeSniffer Pear
Page](http://pear.php.net/package/PHP_CodeSniffer) for information on
installing PHP\_CodeSniffer on your system.

##### Running CodeSniffer

You can run the CodeSniffer by going to the platform root directory and
executing `phpcs --report=checkstyle
      --report-file=build/logs/checkstyle.xml --standard=/path/to/platform/build/phpcs/Joomla /path/to/platform`

Alternatively, if you have Ant installed on your system, you may run the
CodeSniffer by going to the platform root directory and executing
`ant phpcs`

##### Known Issues

-   There is currently an issue with running the Code Sniffer on the
    Simplepie library. Pointing the sniffs at the libraries/joomla
    directory or below will avoid the issue.


