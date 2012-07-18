<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
		return $this->createXMLDataSet(__DIR__.'/stubs/database.xml');
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
		if (class_exists('JSqlSrvTestConfig')) {
			$config = new JSqlSrvTestConfig;
		} else {
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
	 * @todo Implement test__destruct().
	 */
	public function test__destruct()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testConnected().
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
	 * @todo Implement testEscape().
	 */
	public function testEscape()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testGetAffectedRows().
	 */
	public function testGetAffectedRows()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testGetCollation().
	 */
	public function testGetCollation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testGetExporter().
	 */
	public function testGetExporter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('Implement this test when the exporter is added.');
	}

	/**
	 * @todo Implement testGetImporter().
	 */
	public function testGetImporter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('Implement this test when the importer is added.');
	}

	/**
	 * @todo Implement testGetNumRows().
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
	 * @todo Implement testGetTableColumns().
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
			'Line:'.__LINE__.' The getVersion method should return a string containing the driver version.'
		);
	}

	/**
	 * @todo Implement testInsertid().
	 */
	public function testInsertid()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testLoadAssoc().
	 */
	public function testLoadAssoc()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadAssocList().
	 */
	public function testLoadAssocList()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadColumn().
	 */
	public function testLoadColumn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadNextObject().
	 */
	public function testLoadNextObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testLoadNextRow().
	 */
	public function testLoadNextRow()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testLoadObject().
	 */
	public function testLoadObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadObjectList().
	 */
	public function testLoadObjectList()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadResult().
	 */
	public function testLoadResult()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadRow().
	 */
	public function testLoadRow()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testLoadRowList().
	 */
	public function testLoadRowList()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testQuery().
	 */
	public function testQuery()
	{
		// Remove the following lines when you implement this test.
		$this->markTestSkipped('PHPUnit does not support testing queries on SQL Server.');
	}

	/**
	 * @todo Implement testSelect().
	 */
	public function testSelect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testSetUTF().
	 */
	public function testSetUTF()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test Test method - there really isn't a lot to test here, but
	 * this is present for the sake of completeness
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
	 * @todo Implement testUpdateObject().
	 */
	public function testUpdateObject()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
