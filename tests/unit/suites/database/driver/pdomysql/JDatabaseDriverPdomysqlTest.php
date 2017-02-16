<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDatabaseDriverPdomysql.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       3.4
 */
class JDatabaseDriverPdomysqlTest extends TestCaseDatabasePdomysql
{
	/**
	 * Data for the testEscape test.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function dataTestEscape()
	{
		return array(
			array("'%_abc123", false, '\\\'%_abc123'),
			array("'%_abc123", true, '\\\'\\%\_abc123')
		);
	}

	/**
	 * Data for the testQuoteName test.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dataTestQuoteName()
	{
		return array(
			array('protected`title', null, '`protected``title`'),
			array('protected"title', null, '`protected"title`'),
			array('protected]title', null, '`protected]title`'),
		);
	}

	/**
	 * Data for the testTransactionRollback test.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function dataTestTransactionRollback()
	{
		return array(array(null, 0), array('transactionSavepoint', 1));
	}

	/**
	 * Tests the __destruct method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function test__destruct()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the connected method.
	 *
	 * @return  void
	 *
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function testDropTable()
	{
		// Create #__bar table first
		self::$driver->setQuery('CREATE TABLE IF NOT EXISTS `#__bar` (`id` int(10) unsigned NOT NULL);');
		self::$driver->execute();

		// Check return self or not.
		$this->assertThat(
			self::$driver->dropTable('#__bar', true),
			$this->isInstanceOf('JDatabaseDriverPdomysql'),
			'The table is dropped if present.'
		);

		// Check is table dropped.
		self::$driver->setQuery("SHOW TABLES LIKE '%#__bar%'");
		$exists = self::$driver->loadResult();

		$this->assertNull($exists);
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
	 * @since         3.4
	 */
	public function testEscape($text, $extra, $expected)
	{
		$this->assertThat(
			self::$driver->escape($text, $extra),
			$this->equalTo($expected),
			'The string was not escaped properly'
		);
	}

	/**
	 * Test the quoteName method.
	 *
	 * @param   string  $text      The column name or alias to be quote.
	 * @param   string  $asPart    String used for AS query part.
	 * @param   string  $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestQuoteName
	 * @since         __DEPLOY_VERSION__
	 */
	public function testQuoteName($text, $asPart, $expected)
	{
		$this->assertThat(
			self::$driver->quoteName($text, $asPart),
			$this->equalTo($expected),
			'The name was not quoted properly'
		);
	}

	/**
	 * Test getAffectedRows method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetAffectedRows()
	{
		$query = self::$driver->getQuery(true);
		$query->delete();
		$query->from('#__dbtest');
		self::$driver->setQuery($query);

		self::$driver->execute();

		$this->assertThat(
			self::$driver->getAffectedRows(),
			$this->equalTo(4),
			__LINE__
		);
	}

	/**
	 * Test getCollation method.
	 *
	 * @return  void
	 *
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function testGetExporter()
	{
		$this->assertThat(
			self::$driver->getExporter(),
			$this->isInstanceOf('JDatabaseExporterPdomysql'),
			'Line:' . __LINE__ . ' The getExporter method should return the correct exporter.'
		);
	}

	/**
	 * Test getImporter method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetImporter()
	{
		$this->assertThat(
			self::$driver->getImporter(),
			$this->isInstanceOf('JDatabaseImporterPdomysql'),
			'Line:' . __LINE__ . ' The getImporter method should return the correct importer.'
		);
	}

	/**
	 * Test getNumRows method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetNumRows()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description = ' . self::$driver->quote('one'));
		self::$driver->setQuery($query);

		$res = self::$driver->execute();

		$this->assertThat(
			self::$driver->getNumRows($res),
			$this->equalTo(2),
			__LINE__
		);
	}

	/**
	 * Tests the getTableCreate method.
	 *
	 * @return  void
	 *
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function testGetTableColumns()
	{
		$tableCol = array('id' => 'int unsigned', 'title' => 'varchar', 'start_date' => 'datetime', 'description' => 'text');

		$this->assertThat(
			self::$driver->getTableColumns('#__dbtest'),
			$this->equalTo($tableCol),
			__LINE__
		);

		/* Not only type field */
		$id             = new stdClass;
		$id->Default    = null;
		$id->Field      = 'id';
		$id->Type       = 'int(10) unsigned';
		$id->Null       = 'NO';
		$id->Key        = 'PRI';
		$id->Collation  = null;
		$id->Extra      = 'auto_increment';
		$id->Privileges = 'select,insert,update,references';
		$id->Comment    = '';

		$title             = new stdClass;
		$title->Default    = null;
		$title->Field      = 'title';
		$title->Type       = 'varchar(50)';
		$title->Null       = 'NO';
		$title->Key        = '';
		$title->Collation  = 'utf8_general_ci';
		$title->Extra      = '';
		$title->Privileges = 'select,insert,update,references';
		$title->Comment    = '';

		$start_date             = new stdClass;
		$start_date->Default    = null;
		$start_date->Field      = 'start_date';
		$start_date->Type       = 'datetime';
		$start_date->Null       = 'NO';
		$start_date->Key        = '';
		$start_date->Collation  = null;
		$start_date->Extra      = '';
		$start_date->Privileges = 'select,insert,update,references';
		$start_date->Comment    = '';

		$description             = new stdClass;
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
			self::$driver->getTableColumns('#__dbtest', false),
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
	 * @since   3.4
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
	 * @since   3.4
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
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function testInsertid()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test loadAssoc method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testLoadAssoc()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssoc();

		$this->assertThat(
			$result,
			$this->equalTo(
				array(
					'title' => 'Testing'
				)),
				__LINE__
			);
	}

	/**
	 * Test loadAssocList method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testLoadAssocList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
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
	 * @since   3.4
	 */
	public function testLoadColumn()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadColumn();

		$this->assertThat(
			$result,
			$this->equalTo(array('Testing', 'Testing2', 'Testing3', 'Testing4')),
			__LINE__
		);
	}

	/**
	 * Test loadNextObject method.
	 *
	 * @return  void
	 *
	 * @since   3.4
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
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function testLoadObject()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadObject();

		$objCompare              = new stdClass;
		$objCompare->id          = 3;
		$objCompare->title       = 'Testing3';
		$objCompare->start_date  = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$this->assertThat(
			$result,
			$this->equalTo($objCompare),
			__LINE__
		);
	}

	/**
	 * Test loadObjectList method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testLoadObjectList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->order('id');
		self::$driver->setQuery($query);
		$result = self::$driver->loadObjectList();

		$expected = array();

		$objCompare              = new stdClass;
		$objCompare->id          = 1;
		$objCompare->title       = 'Testing';
		$objCompare->start_date  = '1980-04-18 00:00:00';
		$objCompare->description = 'one';

		$expected[] = clone $objCompare;

		$objCompare              = new stdClass;
		$objCompare->id          = 2;
		$objCompare->title       = 'Testing2';
		$objCompare->start_date  = '1980-04-18 00:00:00';
		$objCompare->description = 'one';

		$expected[] = clone $objCompare;

		$objCompare              = new stdClass;
		$objCompare->id          = 3;
		$objCompare->title       = 'Testing3';
		$objCompare->start_date  = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$expected[] = clone $objCompare;

		$objCompare              = new stdClass;
		$objCompare->id          = 4;
		$objCompare->title       = 'Testing4';
		$objCompare->start_date  = '1980-04-18 00:00:00';
		$objCompare->description = 'four';

		$expected[] = clone $objCompare;

		$this->assertThat(
			$result,
			$this->equalTo($expected),
			__LINE__
		);
	}

	/**
	 * Test loadResult method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testLoadResult()
	{
		$query = self::$driver->getQuery(true);
		$query->select('id');
		$query->from('#__dbtest');
		$query->where('title=' . self::$driver->quote('Testing2'));

		self::$driver->setQuery($query);
		$result = self::$driver->loadResult();

		$this->assertThat(
			$result,
			$this->equalTo(2),
			__LINE__
		);

	}

	/**
	 * Test loadRow method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testLoadRow()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRow();

		$expected = array(3, 'Testing3', '1980-04-18 00:00:00', 'three');

		$this->assertThat(
			$result,
			$this->equalTo($expected),
			__LINE__
		);
	}

	/**
	 * Test loadRowList method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testLoadRowList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRowList();

		$expected = array(array(1, 'Testing', '1980-04-18 00:00:00', 'one'), array(2, 'Testing2', '1980-04-18 00:00:00', 'one'));

		$this->assertThat(
			$result,
			$this->equalTo($expected),
			__LINE__
		);
	}

	/**
	 * Test the JDatabaseDriverPdomysql::execute() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testExecute()
	{
		self::$driver->setQuery(
			"REPLACE INTO `jos_dbtest` SET `id` = 5, `title` = 'testTitle', `start_date` = '2014-08-17 00:00:00', `description` = 'testDescription'"
		);

		$this->assertInstanceOf('PDOStatement', self::$driver->execute());

	}

	/**
	 * Tests the renameTable method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testRenameTable()
	{
		$newTableName = 'bak_jos_dbtest';

		self::$driver->renameTable('jos_dbtest', $newTableName);

		// Check name change
		$tableList = self::$driver->getTableList();

		$this->assertThat(
			in_array($newTableName, $tableList),
			$this->isTrue(),
			__LINE__
		);

		// Restore initial state
		self::$driver->renameTable($newTableName, 'jos_dbtest');
	}

	/**
	 * Test select method.
	 *
	 * @return  void
	 *
	 * @since   3.4
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
	 * @since   3.4
	 */
	public function testSetUTF()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the transactionCommit method.
	 *
	 * @return  void
	 *
	 * @since   3.4
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

		/* Check if value is present */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where('id = 6');
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRow();

		$expected = array('6', 'testTitle', '1970-01-01 00:00:00', 'testDescription');

		$this->assertThat(
			$result,
			$this->equalTo($expected),
			__LINE__
		);
	}

	/**
	 * Tests the transactionRollback method, with and without savepoint.
	 *
	 * @param   string  $toSavepoint  Savepoint name to rollback transaction to
	 * @param   int     $tupleCount   Number of tuple found after insertion and rollback
	 *
	 * @return  void
	 *
	 * @since        3.4
	 * @dataProvider dataTestTransactionRollback
	 */
	public function testTransactionRollback($toSavepoint, $tupleCount)
	{
		self::$driver->transactionStart();

		/* Try to insert this tuple, inserted only when savepoint != null */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("7, 'testRollback', '1970-01-01', 'testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		/* Create savepoint only if is passed by data provider */
		if (!is_null($toSavepoint))
		{
			self::$driver->transactionStart((boolean) $toSavepoint);
		}

		/* Try to insert this tuple, always rolled back */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("8, 'testRollback', '1972-01-01', 'testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		self::$driver->transactionRollback((boolean) $toSavepoint);

		/* Release savepoint and commit only if a savepoint exists */
		if (!is_null($toSavepoint))
		{
			self::$driver->transactionCommit();
		}

		/* Find how many rows have description='testRollbackSp' :
		 *   - 0 if a savepoint doesn't exist
		 *   - 1 if a savepoint exists
		 */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where("description = 'testRollbackSp'");
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRowList();

		$this->assertThat(
			count($result),
			$this->equalTo($tupleCount),
			__LINE__
		);
	}

	/**
	 * Test isSupported method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testIsSupported()
	{
		$this->assertThat(
			JDatabaseDriverPdomysql::isSupported(),
			$this->isTrue(),
			__LINE__
		);
	}

	/**
	 * Test updateObject method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testUpdateObject()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
