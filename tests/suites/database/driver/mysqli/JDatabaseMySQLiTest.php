<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDatabaseMySQL.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseMysqliTest extends TestCaseDatabaseMysqli
{
	/**
	 * Data for the testEscape test.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function dataTestEscape()
	{
		return array(
			array("'%_abc123", false, '\\\'%_abc123'),
			array("'%_abc123", true, '\\\'\\%\_abc123')
		);
	}

	/**
	 * Test __destruct method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function test__destruct()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test connected method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testConnected()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the dropTable method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testDropTable()
	{
		$this->assertThat(self::$driver->dropTable('#__bar', true), $this->isInstanceOf('JDatabaseDriverMysqli'), 'The table is dropped if present.');
	}

	/**
	 * Tests the escape method.
	 *
	 * @param   string   $text      The string to be escaped.
	 * @param   boolean  $extra     Optional parameter to provide extra escaping.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestEscape
	 * @since         11.4
	 */
	public function testEscape($text, $extra, $expected)
	{
		$this->assertThat(self::$driver->escape($text, $extra), $this->equalTo($expected), 'The string was not escaped properly');
	}

	/**
	 * Test getAffectedRows method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetAffectedRows()
	{
		$query = self::$driver->getQuery(true);
		$query->delete();
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);

		$result = self::$driver->execute();

		$this->assertThat(self::$driver->getAffectedRows(), $this->equalTo(4), __LINE__);
	}

	/**
	 * Test getExporter method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetExporter()
	{
		$this->assertThat(
			self::$driver->getExporter(),
			$this->isInstanceOf('JDatabaseExporterMysqli'),
			'Line:' . __LINE__ . ' The getExporter method should return the correct exporter.'
		);
	}

	/**
	 * Test getImporter method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetImporter()
	{
		$this->assertThat(
			self::$driver->getImporter(),
			$this->isInstanceOf('JDatabaseImporterMysqli'),
			'Line:' . __LINE__ . ' The getImporter method should return the correct importer.'
		);
	}

	/**
	 * Test getNumRows method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetNumRows()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the getTableCreate method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetTableCreate()
	{
		$this->assertThat(
			self::$driver->getTableCreate('#__dbtest'),
			$this->isType('array'),
			'The statement to create the table is returned in an array.'
		);
	}

	/**
	 * Tests the getTableKeys method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetTableKeys()
	{
		$this->assertThat(
			self::$driver->getTableKeys('#__dbtest'),
			$this->isType('array'),
			'The list of keys for the table is returned in an array.'
		);
	}

	/**
	 * Tests the getTableList method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetTableList()
	{
		$this->assertThat(
			self::$driver->getTableList(),
			$this->isType('array'),
			'The list of tables for the database is returned in an array.'
		);
	}

	/**
	 * Test getVersion method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetVersion()
	{
		$this->assertThat(
			strlen(self::$driver->getVersion()),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getVersion method should return something without error.'
		);
	}

	/**
	 * Test insertid method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testInsertid()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test insertObject method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testInsertObject()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test loadAssoc method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadAssoc()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssoc();

		$this->assertThat($result, $this->equalTo(array('title' => 'Testing')), __LINE__);
	}

	/**
	 * Test loadAssocList method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadAssocList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssocList();

		$this->assertThat(
			$result,
			$this->equalTo(array(array('title' => 'Testing'), array('title' => 'Testing2'), array('title' => 'Testing3'), array('title' => 'Testing4'))),
			__LINE__
		);
	}

	/**
	 * Test loadColumn method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadColumn()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadColumn();

		$this->assertThat($result, $this->equalTo(array('Testing', 'Testing2', 'Testing3', 'Testing4')), __LINE__);
	}

	/**
	 * Test loadNextObject method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadNextObject()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test loadNextRow method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadNextRow()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test loadObject method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadObject()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadObject();

		$objCompare = new stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$this->assertThat($result, $this->equalTo($objCompare), __LINE__);
	}

	/**
	 * Test loadObjectList method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadObjectList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->order('id');
		self::$driver->setQuery($query);
		$result = self::$driver->loadObjectList();

		$expected = array();

		$objCompare = new stdClass;
		$objCompare->id = 1;
		$objCompare->title = 'Testing';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'one';

		$expected[] = clone $objCompare;

		$objCompare = new stdClass;
		$objCompare->id = 2;
		$objCompare->title = 'Testing2';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'one';

		$expected[] = clone $objCompare;

		$objCompare = new stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$expected[] = clone $objCompare;

		$objCompare = new stdClass;
		$objCompare->id = 4;
		$objCompare->title = 'Testing4';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'four';

		$expected[] = clone $objCompare;

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test loadResult method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadResult()
	{
		$query = self::$driver->getQuery(true);
		$query->select('id');
		$query->from('jos_dbtest');
		$query->where('title=' . self::$driver->quote('Testing2'));

		self::$driver->setQuery($query);
		$result = self::$driver->loadResult();

		$this->assertThat($result, $this->equalTo(2), __LINE__);

	}

	/**
	 * Test loadRow method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadRow()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRow();

		$expected = array(3, 'Testing3', '1980-04-18 00:00:00', 'three');

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test loadRowList method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testLoadRowList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRowList();

		$expected = array(array(1, 'Testing', '1980-04-18 00:00:00', 'one'), array(2, 'Testing2', '1980-04-18 00:00:00', 'one'));

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test the JDatabaseDriverMysqli::query() method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testQuery()
	{
		self::$driver->setQuery("REPLACE INTO `jos_dbtest` SET `id` = 5, `title` = 'testTitle'");

		$this->assertThat(self::$driver->execute(), $this->isTrue(), __LINE__);

		$this->assertThat(self::$driver->insertid(), $this->equalTo(5), __LINE__);

	}

	/**
	 * Test select method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testSelect()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test setUTF method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testSetUTF()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test isSupported method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testIsSupported()
	{
		$this->assertThat(JDatabaseDriverMysqli::isSupported(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test updateObject method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testUpdateObject()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.4
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/stubs/database.xml');
	}
}
