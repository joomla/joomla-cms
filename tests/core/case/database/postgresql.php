<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Abstract test case class for Postgresql database testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCaseDatabasePostgresql extends TestCaseDatabase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		// Example DSN would be: host=localhost;dbname=joomla_ut;user=utuser;pass=ut1234
		if (!defined('JTEST_DATABASE_POSTGRESQL_DSN') || getenv('JTEST_DATABASE_POSTGRESQL_DSN') == '')
		{
			$this->markTestSkipped('The JDatabaseDriverPostgresql test DSN has not been defined.');
		}
		else
		{
			$this->_dsn = defined('JTEST_DATABASE_POSTGRESQL_DSN') ? JTEST_DATABASE_POSTGRESQL_DSN : getenv('JTEST_DATABASE_POSTGRESQL_DSN');
		}
	}
}
