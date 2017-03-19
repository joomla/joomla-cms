<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JSchemaChangeitem.
 */
class JSchemaChangeitemTest extends TestCase
{
	/**
	 * Data provider for the getInstance() test case
	 *
	 * @return  array
	 */
	public function dataGetInstance()
	{
		return array(
			'MySQLi'     => array('Mysqli', 'Mysql', 'mysql'),
			'PDO MySQL'  => array('Pdomysql', 'Mysql', 'mysql'),
			'PostgreSQL' => array('Postgresql', 'Postgresql', 'postgresql'),
			'SQL Azure'  => array('Sqlazure', 'sqlazure'),
		);
	}

	/**
	 * @testdox  getInstance() returns the correct object
	 *
	 * @param   string  $dbDriver      The database driver to be mocked
	 * @param   string  $itemSubclass  The subclass of JSchemaChangeitem that is expected
	 * @param   string  $dbFolder      The name of the folder where the stubs are located
	 *
	 * @covers  JSchemaChangeitem::__construct
	 * @covers  JSchemaChangeitem::getInstance
	 *
	 * @dataProvider  dataGetInstance
	 */
	public function testGetInstanceReturnsTheCorrectObject($dbDriver, $itemSubclass, $dbFolder)
	{
		$file    = __DIR__ . '/stubs/' . $dbFolder . '/3.5.0-2016-03-01.sql';
		$dbo     = $this->getMockDatabase($dbDriver);
		$queries = JDatabaseDriver::splitSql(file_get_contents($file));

		$item = JSchemaChangeitem::getInstance($dbo, $file, $queries[0]);

		$this->assertInstanceOf('JSchemaChangeitem' . $itemSubclass, $item, 'The correct JSchemaChangeitem subclass was not instantiated');
	}

	/**
	 * @testdox  getInstance() throws an Exception for an unsupported driver
	 *
	 * @covers   JSchemaChangeitem::getInstance
	 *
	 * @expectedException  RuntimeException
	 */
	public function testGetInstanceThrowsAnExceptionForAnUnsupportedDriver()
	{
		$file  = __DIR__ . '/stubs/mysql/3.5.0-2016-03-01.sql';
		$dbo   = $this->getMockDatabase('Sqlite');
		$query = 'SELECT foo FROM bar';

		JSchemaChangeitem::getInstance($dbo, $file, $query);
	}
}
