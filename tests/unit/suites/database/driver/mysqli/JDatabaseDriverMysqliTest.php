<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDatabaseDriverMysqli.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseDriverMysqliTest extends TestCaseDatabaseMysqli
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
	 * Data for the testTransactionRollback test.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function dataTestTransactionRollback()
	{
		return array(array(null, 0), array('transactionSavepoint', 1));
	}

	/**
	 * Data for testLoadNextObject test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestLoadNextObject()
	{
		$objCompOne = new stdClass;
		$objCompOne->id = 1;
		$objCompOne->title = 'Testing';
		$objCompOne->start_date = '1980-04-18 00:00:00';
		$objCompOne->description = 'one';

		$objCompTwo = new stdClass;
		$objCompTwo->id = 2;
		$objCompTwo->title = 'Testing2';
		$objCompTwo->start_date = '1980-04-18 00:00:00';
		$objCompTwo->description = 'one';

		$objCompThree = new stdClass;
		$objCompThree->id = 3;
		$objCompThree->title = 'Testing3';
		$objCompThree->start_date = '1980-04-18 00:00:00';
		$objCompThree->description = 'three';

		$objCompFour = new stdClass;
		$objCompFour->id = 4;
		$objCompFour->title = 'Testing4';
		$objCompFour->start_date = '1980-04-18 00:00:00';
		$objCompFour->description = 'four';

		return array(array(array($objCompOne, $objCompTwo, $objCompThree, $objCompFour)));
	}

	/**
	 * Data for testLoadNextRow test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestLoadNextRow()
	{
		return array(
			array(
				array(
					array(1, 'Testing', '1980-04-18 00:00:00', 'one'),
					array(2, 'Testing2', '1980-04-18 00:00:00', 'one'),
					array(3, 'Testing3', '1980-04-18 00:00:00', 'three'),
					array(4, 'Testing4', '1980-04-18 00:00:00', 'four'))));
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
	 * Test getCollation method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetCollation()
	{
		$this->assertThat(
			self::$driver->getCollation(),
			$this->equalTo('utf8_general_ci'),
			'Line:' . __LINE__ . ' The getCollation method should return the collation of the database.'
		);
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
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description = ' . self::$driver->quote('one'));
		self::$driver->setQuery($query);

		$res = self::$driver->execute();

		$this->assertThat(self::$driver->getNumRows($res), $this->equalTo(2), __LINE__);
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
	 * Test getTableColumns method.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testGetTableColumns()
	{
		$tableCol = array('id' => 'int unsigned', 'title' => 'varchar', 'start_date' => 'datetime', 'description' => 'text');

		$this->assertThat(
			self::$driver->getTableColumns('jos_dbtest'),
			$this->equalTo($tableCol),
			__LINE__
		);

		/* not only type field */
		$id = new stdClass;
		$id->Default    = null;
		$id->Field      = 'id';
		$id->Type       = 'int(10) unsigned';
		$id->Null       = 'NO';
		$id->Key        = 'PRI';
		$id->Collation  = null;
		$id->Extra      = 'auto_increment';
		$id->Privileges = 'select,insert,update,references';
		$id->Comment    = '';

		$title = new stdClass;
		$title->Default    = null;
		$title->Field      = 'title';
		$title->Type       = 'varchar(50)';
		$title->Null       = 'NO';
		$title->Key        = '';
		$title->Collation  = 'utf8_general_ci';
		$title->Extra      = '';
		$title->Privileges = 'select,insert,update,references';
		$title->Comment    = '';

		$start_date = new stdClass;
		$start_date->Default    = null;
		$start_date->Field      = 'start_date';
		$start_date->Type       = 'datetime';
		$start_date->Null       = 'NO';
		$start_date->Key        = '';
		$start_date->Collation  = null;
		$start_date->Extra      = '';
		$start_date->Privileges = 'select,insert,update,references';
		$start_date->Comment    = '';

		$description = new stdClass;
		$description->Default    = null;
		$description->Field      = 'description';
		$description->Type       = 'text';
		$description->Null       = 'NO';
		$description->Key        = '';
		$description->Collation  = 'utf8_general_ci';
		$description->Extra      = '';
		$description->Privileges = 'select,insert,update,references';
		$description->Comment    = '';

		$this->assertThat(
			self::$driver->getTableColumns('jos_dbtest', false),
			$this->equalTo(
				array(
					'id' => $id,
					'title' => $title,
					'start_date' => $start_date,
					'description' => $description
				)
			),
			__LINE__
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
	 * Test loadNextObject function
	 *
	 * @param   array  $objArr  Array of expected objects
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestLoadNextObject
	 */
	public function testLoadNextObject($objArr)
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		$this->assertEquals($objArr[0], self::$driver->loadNextObject());
		$this->assertEquals($objArr[1], self::$driver->loadNextObject());
		$this->assertEquals($objArr[2], self::$driver->loadNextObject());
		$this->assertEquals($objArr[3], self::$driver->loadNextObject());

		/* last call to free cursor, asserting that returns false */
		$this->assertFalse(self::$driver->loadNextObject());
	}

	/**
	 * Test loadNextObject function with preceding loadObject call
	 *
	 * @param   array  $objArr  Array of expected objects
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestLoadNextObject
	 */
	public function testLoadNextObject_plusLoad($objArr)
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		self::$driver->loadObject();

		$this->assertEquals($objArr[0], self::$driver->loadNextObject());
		$this->assertEquals($objArr[1], self::$driver->loadNextObject());
		$this->assertEquals($objArr[2], self::$driver->loadNextObject());
		$this->assertEquals($objArr[3], self::$driver->loadNextObject());

		/* last call to free cursor, asserting that returns false */
		$this->assertFalse(self::$driver->loadNextObject());
	}

	/**
	 * Test loadNextObject function with preceding query call
	 *
	 * @param   array  $objArr  Array of expected objects
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestLoadNextObject
	 */
	public function testLoadNextObject_plusQuery($objArr)
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		self::$driver->execute();

		$this->assertEquals($objArr[0], self::$driver->loadNextObject());
		$this->assertEquals($objArr[1], self::$driver->loadNextObject());
		$this->assertEquals($objArr[2], self::$driver->loadNextObject());
		$this->assertEquals($objArr[3], self::$driver->loadNextObject());

		/* last call to free cursor, asserting that returns false */
		$this->assertFalse(self::$driver->loadNextObject());
	}

	/**
	 * Test loadNextRow function
	 *
	 * @param   array  $rowArr  Array of expected arrays
	 *
	 * @return   void
	 *
	 * @dataProvider dataTestLoadNextRow
	 */
	public function testLoadNextRow($rowArr)
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		$this->assertEquals($rowArr[0], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[1], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[2], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[3], self::$driver->loadNextRow());

		/* last call to free cursor, asserting that returns false */
		$this->assertFalse(self::$driver->loadNextRow());
	}

	/**
	 * Test loadNextRow function with preceding query call
	 *
	 * @param   array  $rowArr  Array of expected arrays
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestLoadNextRow
	 */
	public function testLoadNextRow_plusQuery($rowArr)
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		self::$driver->execute();

		$this->assertEquals($rowArr[0], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[1], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[2], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[3], self::$driver->loadNextRow());

		/* last call to free cursor, asserting that returns false */
		$this->assertFalse(self::$driver->loadNextRow());
	}

	/**
	 * Test loadNextRow function with preceding loadRow call
	 *
	 * @param   array  $rowArr  Array of expected arrays
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestLoadNextRow
	 */
	public function testLoadNextRow_plusLoad($rowArr)
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		self::$driver->loadRow();

		$this->assertEquals($rowArr[0], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[1], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[2], self::$driver->loadNextRow());
		$this->assertEquals($rowArr[3], self::$driver->loadNextRow());

		/* last call to free cursor, asserting that returns false */
		$this->assertFalse(self::$driver->loadNextRow());
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
	 * Test the JDatabaseDriverMysqli::execute() method
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	public function testExecute()
	{
		self::$driver->setQuery("REPLACE INTO `jos_dbtest` SET `id` = 5, `title` = 'testTitle'");

		$this->assertThat(self::$driver->execute(), $this->isTrue(), __LINE__);

		$this->assertThat(self::$driver->insertid(), $this->equalTo(5), __LINE__);
	}

	/**
	 * Test the JDatabaseDriverMysqli::execute() method when there is a limit set
	 * in the query object that respects the JDatabaseQueryLimitable interface
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testExecuteWithLimitsInQuery()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		$query->setLimit(1);
		self::$driver->setQuery($query);
		$result = self::$driver->loadRowList();

		$expected = array(array(1, 'Testing', '1980-04-18 00:00:00', 'one'));

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test the JDatabaseDriverMysqli::execute() method when there is a limit set
	 * in the query object that respects the setQuery method.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testExecuteWithLimitsInSetQuery()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		self::$driver->setQuery($query, 0, 1);
		$result = self::$driver->loadRowList();

		$expected = array(array(1, 'Testing', '1980-04-18 00:00:00', 'one'));

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}


	/**
	 * Tests the renameTable method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testRenameTable()
	{
		$newTableName = 'bak_jos_dbtest';

		self::$driver->renameTable('jos_dbtest', $newTableName);

		// Check name change
		$tableList = self::$driver->getTableList();
		$this->assertThat(in_array($newTableName, $tableList), $this->isTrue(), __LINE__);

		// Restore initial state
		self::$driver->renameTable($newTableName, 'jos_dbtest');
	}

	/**
	 * Tests the transactionCommit method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testTransactionCommit()
	{
		self::$driver->transactionStart();
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("6, 'testTitle', '1970-01-01', 'testDescription'");

		self::$driver->setQuery($queryIns)->execute();

		self::$driver->transactionCommit();

		/* check if value is present */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where('id = 6');
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRow();

		$expected = array('6', 'testTitle', '1970-01-01 00:00:00', 'testDescription');

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Tests the transactionRollback method, with and without savepoint.
	 *
	 * @param   string  $toSavepoint  Savepoint name to rollback transaction to
	 * @param   int     $tupleCount   Number of tuple found after insertion and rollback
	 *
	 * @return  void
	 *
	 * @since        3.1
	 * @dataProvider dataTestTransactionRollback
	 */
	public function testTransactionRollback($toSavepoint, $tupleCount)
	{
		self::$driver->transactionStart();

		/* try to insert this tuple, inserted only when savepoint != null */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("7, 'testRollback', '1970-01-01', 'testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		/* create savepoint only if is passed by data provider */
		if (!is_null($toSavepoint))
		{
			self::$driver->transactionStart((boolean) $toSavepoint);
		}

		/* try to insert this tuple, always rolled back */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("8, 'testRollback', '1972-01-01', 'testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		self::$driver->transactionRollback((boolean) $toSavepoint);

		/* release savepoint and commit only if a savepoint exists */
		if (!is_null($toSavepoint))
		{
			self::$driver->transactionCommit();
		}

		/* find how many rows have description='testRollbackSp' :
		 *   - 0 if a savepoint doesn't exist
		 *   - 1 if a savepoint exists
		 */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where("description = 'testRollbackSp'");
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRowList();

		$this->assertThat(count($result), $this->equalTo($tupleCount), __LINE__);
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
}
