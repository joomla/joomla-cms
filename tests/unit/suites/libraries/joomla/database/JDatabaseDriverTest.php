<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/stubs/nosqldriver.php';

/**
 * Test class for JDatabaseDriver.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       3.1
 */
class JDatabaseDriverTest extends TestCaseDatabase
{
	/**
	 * @var    JDatabaseDriver
	 * @since  3.1
	 */
	protected $db;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		$this->db = JDatabaseDriver::getInstance(
			array(
				'driver' => 'nosql',
				'database' => 'europa',
				'prefix' => '&',
			)
		);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->db);
		parent::tearDown();
	}

	/**
	 * Test for the JDatabaseDriver::__call method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__callQuote()
	{
		$this->assertThat(
			$this->db->q('foo'),
			$this->equalTo($this->db->quote('foo')),
			'Tests the q alias of quote.'
		);
	}

	/**
	 * Test for the JDatabaseDriver::__call method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__callQuoteName()
	{
		$this->assertThat(
			$this->db->qn('foo'),
			$this->equalTo($this->db->quoteName('foo')),
			'Tests the qn alias of quoteName.'
		);
	}

	/**
	 * Test for the JDatabaseDriver::__call method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__callUnknown()
	{
		$this->assertThat(
			$this->db->foo(),
			$this->isNull(),
			'Tests for an unknown method.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::getConnection method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetConnection()
	{
		TestReflection::setValue($this->db, 'connection', 'foo');

		$this->assertThat(
			$this->db->getConnection(),
			$this->equalTo('foo')
		);
	}

	/**
	 * Tests the JDatabaseDriver::getConnectors method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetConnectors()
	{
		$db = $this->db;

		$this->assertContains(
			'sqlite',
			$db::getConnectors(),
			'The getConnectors method should return an array with Sqlite as an available option.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::getCount method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetCount()
	{
		TestReflection::setValue($this->db, 'count', 42);

		$this->assertThat(
			$this->db->getCount(),
			$this->equalTo(42)
		);
	}

	/**
	 * Tests the JDatabaseDriver::getDatabase method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetDatabase()
	{
		$this->assertThat(
			TestReflection::invoke($this->db, 'getDatabase'),
			$this->equalTo('europa')
		);
	}

	/**
	 * Tests the JDatabaseDriver::getDateFormat method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetDateFormat()
	{
		$this->assertThat(
			$this->db->getDateFormat(),
			$this->equalTo('Y-m-d H:i:s')
		);
	}

	/**
	 * Tests the JDatabaseDriver::splitSql method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testSplitSql()
	{
		$db = $this->db;

		$this->assertThat(
			$db::splitSql('SELECT * FROM #__foo;SELECT * FROM #__bar;'),
			$this->equalTo(
				array(
					'SELECT * FROM #__foo;',
					'SELECT * FROM #__bar;'
				)
			),
			'splitSql method should split a string of multiple queries into an array.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::getLog method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetLog()
	{
		TestReflection::setValue($this->db, 'log', 'foo');

		$this->assertThat(
			$this->db->getLog(),
			$this->equalTo('foo')
		);
	}

	/**
	 * Tests the JDatabaseDriver::getPrefix method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetPrefix()
	{
		$this->assertThat(
			$this->db->getPrefix(),
			$this->equalTo('&')
		);
	}

	/**
	 * Tests the JDatabaseDriver::getNullDate method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetNullDate()
	{
		$this->assertThat(
			$this->db->getNullDate(),
			$this->equalTo('1BC')
		);
	}

	/**
	 * Tests the JDatabaseDriver::getMinimum method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetMinimum()
	{
		$this->assertThat(
			$this->db->getMinimum(),
			$this->equalTo('12.1'),
			'getMinimum should return a string with the minimum supported database version number'
		);
	}

	/**
	 * Tests the JDatabaseDriver::isMinimumVersion method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testIsMinimumVersion()
	{
		$this->assertThat(
			$this->db->isMinimumVersion(),
			$this->isTrue(),
			'isMinimumVersion should return a boolean true if the database version is supported by the driver'
		);
	}

	/**
	 * Tests the JDatabaseDriver::setDebug method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testSetDebug()
	{
		$this->assertThat(
			$this->db->setDebug(true),
			$this->isType('boolean'),
			'setDebug should return a boolean value containing the previous debug state.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::setQuery method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testSetQuery()
	{
		$this->assertThat(
			$this->db->setQuery('SELECT * FROM #__dbtest'),
			$this->isInstanceOf('JDatabaseDriver'),
			'setQuery method should return an instance of JDatabaseDriver.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::replacePrefix method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testReplacePrefix()
	{
		$this->assertEquals(
			'SELECT * FROM &dbtest',
			$this->db->replacePrefix('SELECT * FROM #__dbtest'),
			'replacePrefix method should return the query string with the #__ prefix replaced by the actual table prefix.'
		);

		// Prefix in quoted values not replaced, see https://github.com/joomla/joomla-cms/issues/7162
		$this->assertEquals(
			"SHOW TABLE STATUS LIKE '#__table'",
			$this->db->replacePrefix("SHOW TABLE STATUS LIKE '#__table'"),
			'replacePrefix method should not change the #__ prefix in a quoted value.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::quote method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseDriver::quote
	 * @since   11.4
	 */
	public function testQuote()
	{
		$this->assertThat(
			$this->db->quote('test', false),
			$this->equalTo("'test'"),
			'Tests the without escaping.'
		);

		$this->assertThat(
			$this->db->quote('test'),
			$this->equalTo("'-test-'"),
			'Tests the with escaping (default).'
		);

		$this->assertEquals(
			array("'-test1-'", "'-test2-'"),
			$this->db->quote(array('test1', 'test2')),
			'Check that the array is quoted.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::quote method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuoteBooleanTrue()
	{
		$this->assertThat(
			$this->db->quote(true),
			$this->equalTo("'-1-'"),
			'Tests handling of boolean true with escaping (default).'
		);
	}

	/**
	 * Tests the JDatabaseDriver::quote method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuoteBooleanFalse()
	{
		$this->assertThat(
			$this->db->quote(false),
			$this->equalTo("'--'"),
			'Tests handling of boolean false with escaping (default).'
		);
	}

	/**
	 * Tests the JDatabaseDriver::quote method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuoteNull()
	{
		$this->assertThat(
			$this->db->quote(null),
			$this->equalTo("'--'"),
			'Tests handling of null with escaping (default).'
		);
	}
	/**
	 * Tests the JDatabaseDriver::quote method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuoteInteger()
	{
		$this->assertThat(
			$this->db->quote(42),
			$this->equalTo("'-42-'"),
			'Tests handling of integer with escaping (default).'
		);
	}

	/**
	 * Tests the JDatabaseDriver::quote method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuoteFloat()
	{
		$this->assertThat(
			$this->db->quote(3.14),
			$this->equalTo("'-3.14-'"),
			'Tests handling of float with escaping (default).'
		);
	}

	/**
	 * Tests the JDatabaseDriver::quoteName method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuoteName()
	{
		$this->assertThat(
			$this->db->quoteName('test'),
			$this->equalTo('[test]'),
			'Tests the left-right quotes on a string.'
		);

		$this->assertThat(
			$this->db->quoteName('a.test'),
			$this->equalTo('[a].[test]'),
			'Tests the left-right quotes on a dotted string.'
		);

		$this->assertThat(
			$this->db->quoteName(array('a', 'test')),
			$this->equalTo(array('[a]', '[test]')),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->db->quoteName(array('a.b', 'test.quote')),
			$this->equalTo(array('[a].[b]', '[test].[quote]')),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->db->quoteName(array('a.b', 'test.quote'), array(null, 'alias')),
			$this->equalTo(array('[a].[b]', '[test].[quote] AS [alias]')),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->db->quoteName(array('a.b', 'test.quote'), array('alias1', 'alias2')),
			$this->equalTo(array('[a].[b] AS [alias1]', '[test].[quote] AS [alias2]')),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->db->quoteName((object) array('a', 'test')),
			$this->equalTo(array('[a]', '[test]')),
			'Tests the left-right quotes on an object.'
		);

		TestReflection::setValue($this->db, 'nameQuote', '/');

		$this->assertThat(
			$this->db->quoteName('test'),
			$this->equalTo('/test/'),
			'Tests the uni-quotes on a string.'
		);
	}

	/**
	 * Tests the JDatabaseDriver::truncateTable method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testTruncateTable()
	{
		$this->assertNull(
			$this->db->truncateTable('#__dbtest'),
			'truncateTable should not return anything if successful.'
		);
	}
}
