<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDatabaseSQLSrv.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       12.1
 */
class JDatabaseSQLSrvTest extends TestCaseDatabase
{
	/**
	 * @var    JDatabaseSQLSrv
	 * @since  12.1
	 */
	protected $object;

	/**
	 * Data for the testEscape test.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function dataTestEscape()
	{
		return array(
			array("'%_abc123", false, '\\\'%_abc123'),
			array("'%_abc123", true, '\\\'\\%\_abc123'),
		);
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   12.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/stubs/database.xml');
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		@include_once JPATH_TESTS . '/config_sqlsrv.php';

		if (class_exists('JSqlSrvTestConfig'))
		{
			$config = new JSqlSrvTestConfig;
		}
		else
		{
			$this->markTestSkipped('There is no SQL Server test config file present.');
		}
		$this->object = JDatabase::getInstance(
			array(
				'driver' => $config->dbtype,
				'database' => $config->db,
				'host' => $config->host,
				'user' => $config->user,
				'password' => $config->password
			)
		);

		parent::setUp();
	}

	/**
	 * Test...
	 *
	 * @todo Implement test__destruct().
	 *
	 * @return void
	 */
	public function test__destruct()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testConnected().
	 *
	 * @return void
	 */
	public function testConnected()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the JDatabaseSQLSrv dropTable method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testDropTable()
	{
		$this->assertThat(
			$this->object->dropTable('#__bar', true),
			$this->isInstanceOf('JDatabaseDriverSqlsrv'),
			'The table is dropped if present.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testEscape().
	 *
	 * @return void
	 */
	public function testEscape()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetAffectedRows().
	 *
	 * @return void
	 */
	public function testGetAffectedRows()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetCollation().
	 *
	 * @return void
	 */
	public function testGetCollation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetExporter().
	 *
	 * @return void
	 */
	public function testGetExporter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('Implement this test when the exporter is added.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetImporter().
	 *
	 * @return void
	 */
	public function testGetImporter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('Implement this test when the importer is added.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetNumRows().
	 *
	 * @return void
	 */
	public function testGetNumRows()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the JDatabaseSQLSrv getTableCreate method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetTableCreate()
	{
		$this->assertThat(
			$this->object->getTableCreate('#__dbtest'),
			$this->isType('string'),
			'A blank string is returned since this is not supported on SQL Server.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetTableColumns().
	 *
	 * @return void
	 */
	public function testGetTableColumns()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the JDatabaseSQLSrv getTableKeys method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetTableKeys()
	{
		$this->assertThat(
			$this->object->getTableKeys('#__dbtest'),
			$this->isType('array'),
			'The list of keys for the table is returned in an array.'
		);
	}

	/**
	 * Tests the JDatabaseSQLSrv getTableList method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetTableList()
	{
		$this->assertThat(
			$this->object->getTableList(),
			$this->isType('array'),
			'The list of tables for the database is returned in an array.'
		);
	}

	/**
	 * Test getVersion method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetVersion()
	{
		$this->assertThat(
			$this->object->getVersion(),
			$this->isType('string'),
			'Line:' . __LINE__ . ' The getVersion method should return a string containing the driver version.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testInsertid().
	 *
	 * @return void
	 */
	public function testInsertid()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadAssoc().
	 *
	 * @return void
	 */
	public function testLoadAssoc()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadAssocList().
	 *
	 * @return void
	 */
	public function testLoadAssocList()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadColumn().
	 *
	 * @return void
	 */
	public function testLoadColumn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadNextObject().
	 *
	 * @return void
	 */
	public function testLoadNextObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadNextRow().
	 *
	 * @return void
	 */
	public function testLoadNextRow()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadObject().
	 *
	 * @return void
	 */
	public function testLoadObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadObjectList().
	 *
	 * @return void
	 */
	public function testLoadObjectList()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadResult().
	 *
	 * @return void
	 */
	public function testLoadResult()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadRow().
	 *
	 * @return void
	 */
	public function testLoadRow()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testLoadRowList().
	 *
	 * @return void
	 */
	public function testLoadRowList()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testQuery().
	 *
	 * @return void
	 */
	public function testQuery()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSelect().
	 *
	 * @return void
	 */
	public function testSelect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSetUTF().
	 *
	 * @return void
	 */
	public function testSetUTF()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test Test method - there really isn't a lot to test here, but
	 * this is present for the sake of completeness
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		$this->assertThat(
			JDatabaseSqlsrv::isSupported(),
			$this->isTrue(),
			__LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testUpdateObject().
	 *
	 * @return void
	 */
	public function testUpdateObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
