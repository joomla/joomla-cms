<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDatabaseDriverPostgresql.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.3
 */
class JDatabaseDriverPostgresqlTest extends TestCaseDatabasePostgresql
{
	/**
	 * Data for the testEscape test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestEscape()
	{
		return array(
			/* ' will be escaped and become '' */
			array("'%_abc123", false, '\'\'%_abc123'),
			array("'%_abc123", true, '\'\'\%\_abc123'),
			/* ' and \ will be escaped: the first become '', the latter \\ */
			array("\'%_abc123", false, '\\\\\'\'%_abc123'),
			array("\'%_abc123", true, '\\\\\'\'\%\_abc123'));
	}

	/**
	 * Data for the testGetEscaped test, proxies of escape, so same data test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestGetEscaped()
	{
		return array(
			/* ' will be escaped and become '' */
			array("'%_abc123", false), array("'%_abc123", true),
			/* ' and \ will be escaped: the first become '', the latter \\ */
			array("\'%_abc123", false), array("\'%_abc123", true));
	}

	/**
	 * Data for the testTransactionRollback test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestTransactionRollback()
	{
		return array(array(null, 0), array('transactionSavepoint', 1));
	}

	/**
	 * Data for the getCreateDbQuery test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataGetCreateDbQuery()
	{
		$obj = new stdClass;
		$obj->db_user = 'testName';
		$obj->db_name = 'testDb';

		return array(array($obj, false), array($obj, true));
	}

	/**
	 * Data for the TestReplacePrefix test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestReplacePrefix()
	{
		return array(
			/* no prefix inside, no change */
			array('SELECT * FROM table', '#__', 'SELECT * FROM table'),
			/* the prefix inside double quote has to be changed */
			array('SELECT * FROM "#__table"', '#__', 'SELECT * FROM "jos_table"'),
			/* the prefix inside single quote hasn't to be changed */
			array('SELECT * FROM \'#__table\'', '#__', 'SELECT * FROM \'#__table\''),
			/* mixed quote case */
			array('SELECT * FROM \'#__table\', "#__tableSecond"', '#__', 'SELECT * FROM \'#__table\', "jos_tableSecond"'),
			/* the prefix used in sequence name (single quote) has to be changed */
			array('SELECT * FROM currval(\'#__table_id_seq\'::regclass)', '#__', 'SELECT * FROM currval(\'jos_table_id_seq\'::regclass)'),
			/* using another prefix */
			array('SELECT * FROM "#!-_table"', '#!-_', 'SELECT * FROM "jos_table"'));
	}

	/**
	 * Data for testQuoteName test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestQuoteName()
	{
		return array(
			/* no dot inside var */
			array('jos_dbtest', null, '"jos_dbtest"'),
			/* a dot inside var */
			array('public.jos_dbtest', null, '"public"."jos_dbtest"'),
			/* two dot inside var */
			array('joomla_ut.public.jos_dbtest', null, '"joomla_ut"."public"."jos_dbtest"'),
			/* using an array */
			array(array('joomla_ut', 'dbtest'), null, array('"joomla_ut"', '"dbtest"')),
			/* using an array with dotted name */
			array(array('joomla_ut.dbtest', 'public.dbtest'), null, array('"joomla_ut"."dbtest"', '"public"."dbtest"')),
			/* using an array with two dot in name */
			array(array('joomla_ut.public.dbtest', 'public.dbtest.col'), null, array('"joomla_ut"."public"."dbtest"', '"public"."dbtest"."col"')),

			/*** same tests with AS part ***/
			array('jos_dbtest', 'test', '"jos_dbtest" AS "test"'),
			array('public.jos_dbtest', 'tst', '"public"."jos_dbtest" AS "tst"'),
			array('joomla_ut.public.jos_dbtest', 'tst', '"joomla_ut"."public"."jos_dbtest" AS "tst"'),
			array(array('joomla_ut', 'dbtest'), array('j_ut', 'tst'), array('"joomla_ut" AS "j_ut"', '"dbtest" AS "tst"')),
			array(
				array('joomla_ut.dbtest', 'public.dbtest'),
				array('j_ut_db', 'pub_tst'),
				array('"joomla_ut"."dbtest" AS "j_ut_db"', '"public"."dbtest" AS "pub_tst"')),
			array(
				array('joomla_ut.public.dbtest', 'public.dbtest.col'),
				array('j_ut_p_db', 'pub_tst_col'),
				array('"joomla_ut"."public"."dbtest" AS "j_ut_p_db"', '"public"."dbtest"."col" AS "pub_tst_col"')),
			/* last test but with one null inside array */
			array(
				array('joomla_ut.public.dbtest', 'public.dbtest.col'),
				array('j_ut_p_db', null),
				array('"joomla_ut"."public"."dbtest" AS "j_ut_p_db"', '"public"."dbtest"."col"')));
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
	 * Check if connected() method returns true.
	 *
	 * @return   void
	 */
	public function testConnected()
	{
		$this->assertTrue(self::$driver->connected(), 'Not connected to database');
	}

	/**
	 * Tests the JDatabasePostgresql escape method.
	 *
	 * @param   string  $text    The string to be escaped.
	 * @param   bool    $extra   Optional parameter to provide extra escaping.
	 * @param   string  $result  Correct string escaped
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider  dataTestEscape
	 */
	public function testEscape($text, $extra, $result)
	{
		$this->assertEquals($result, self::$driver->escape($text, $extra), 'The string was not escaped properly');
	}

	/**
	 * Test getAffectedRows method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetAffectedRows()
	{
		$query = self::$driver->getQuery(true);
		$query->delete();
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		self::$driver->execute();

		$this->assertEquals(4, self::$driver->getAffectedRows());
	}

	/**
	 * Tests the JDatabasePostgresql getCollation method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetCollation()
	{
		$this->assertNotEmpty(self::$driver->getCollation());
	}

	/**
	 * Tests the JDatabasePostgresql getNumRows method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetNumRows()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		self::$driver->setQuery($query);

		$res = self::$driver->execute();

		$this->assertEquals(2, self::$driver->getNumRows($res));
	}

	/**
	 * Tests the JDatabasePostgresql getTableCreate method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetTableCreate()
	{
		$this->assertEmpty(
			self::$driver->getTableCreate('jos_dbtest')
		);
	}

	/**
	 * Test getTableColumns function.
	 *
	 * @return   void
	 */
	public function testGetTableColumns()
	{
		$tableCol = array('id' => 'integer', 'title' => 'character varying', 'start_date' => 'timestamp without time zone', 'description' => 'text');

		$this->assertEquals($tableCol, self::$driver->getTableColumns('jos_dbtest'));

		/* not only type field */
		$id = new stdClass;
		$id->column_name = 'id';
		$id->Field = 'id';
		$id->type = 'integer';
		$id->Type = 'integer';
		$id->null = 'NO';
		$id->Null = 'NO';
		$id->Default = 'nextval(\'jos_dbtest_id_seq\'::regclass)';
		$id->comments = '';

		$title = new stdClass;
		$title->column_name = 'title';
		$title->Field = 'title';
		$title->type = 'character varying(50)';
		$title->Type = 'character varying(50)';
		$title->null = 'NO';
		$title->Null = 'NO';
		$title->Default = null;
		$title->comments = '';

		$start_date = new stdClass;
		$start_date->column_name = 'start_date';
		$start_date->Field = 'start_date';
		$start_date->type = 'timestamp without time zone';
		$start_date->Type = 'timestamp without time zone';
		$start_date->null = 'NO';
		$start_date->Null = 'NO';
		$start_date->Default = null;
		$start_date->comments = '';

		$description = new stdClass;
		$description->column_name = 'description';
		$description->Field = 'description';
		$description->type = 'text';
		$description->Type = 'text';
		$description->null = 'NO';
		$description->Null = 'NO';
		$description->Default = null;
		$description->comments = '';

		$this->assertEquals(
			array('id' => $id, 'title' => $title, 'start_date' => $start_date, 'description' => $description),
			self::$driver->getTableColumns('jos_dbtest', false)
		);
	}

	/**
	 * Test getTableKeys function.
	 *
	 * @return   void
	 */
	public function testGetTableKeys()
	{
		$pkey = new stdClass;
		$pkey->idxName = 'jos_assets_pkey';
		$pkey->isPrimary = 't';
		$pkey->isUnique = 't';
		$pkey->Query = 'ALTER TABLE jos_assets ADD PRIMARY KEY (id)';

		$asset = new stdClass;
		$asset->idxName = 'idx_asset_name';
		$asset->isPrimary = 'f';
		$asset->isUnique = 't';
		$asset->Query = 'CREATE UNIQUE INDEX idx_asset_name ON jos_assets USING btree (name)';

		$lftrgt = new stdClass;
		$lftrgt->idxName = 'jos_assets_idx_lft_rgt';
		$lftrgt->isPrimary = 'f';
		$lftrgt->isUnique = 'f';
		$lftrgt->Query = 'CREATE INDEX jos_assets_idx_lft_rgt ON jos_assets USING btree (lft, rgt)';

		$id = new stdClass;
		$id->idxName = 'jos_assets_idx_parent_id';
		$id->isPrimary = 'f';
		$id->isUnique = 'f';
		$id->Query = 'CREATE INDEX jos_assets_idx_parent_id ON jos_assets USING btree (parent_id)';

		$this->assertEquals(array($pkey, $id, $lftrgt, $asset), self::$driver->getTableKeys('jos_assets'));
	}

	/**
	 * Test getTableSequences function.
	 *
	 * @return   void
	 */
	public function testGetTableSequences()
	{
		$seq = new stdClass;
		$seq->sequence = 'jos_dbtest_id_seq';
		$seq->schema = 'public';
		$seq->table = 'jos_dbtest';
		$seq->column = 'id';
		$seq->data_type = 'bigint';

		if (version_compare(self::$driver->getVersion(), '9.1.0') >= 0)
		{
			$seq->start_value = '1';
			$seq->minimum_value = '1';
			$seq->maximum_value = '9223372036854775807';
			$seq->increment = '1';
			$seq->cycle_option = 'NO';
		}
		else
		{
			$seq->minimum_value = null;
			$seq->maximum_value = null;
			$seq->increment = null;
			$seq->cycle_option = null;
		}

		$this->assertEquals(array($seq), self::$driver->getTableSequences('jos_dbtest'));
	}

	/**
	 * Tests the JDatabasePostgresql getTableList method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetTableList()
	{
		$expected = array(
			"0" => "jos_assets",
			"1" => "jos_categories",
			"2" => "jos_content",
			"3" => "jos_core_log_searches",
			"4" => "jos_dbtest",
			"5" => "jos_extensions",
			"6" => "jos_languages",
			"7" => "jos_log_entries",
			"8" => "jos_menu",
			"9" => "jos_menu_types",
			"10" => "jos_modules",
			"11" => "jos_modules_menu",
			"12" => "jos_schemas",
			"13" => "jos_session",
			"14" => "jos_update_categories",
			"15" => "jos_update_sites",
			"16" => "jos_update_sites_extensions",
			"17" => "jos_updates",
			"18" => "jos_user_profiles",
			"19" => "jos_user_usergroup_map",
			"20" => "jos_usergroups",
			"21" => "jos_users",
			"22" => "jos_viewlevels");

		$result = self::$driver->getTableList();

		// Assert array size
		$this->assertCount(count($expected), $result);

		// Clear found element to check if all elements are present in any order
		foreach ($result as $k => $v)
		{
			if (in_array($v, $expected))
			{
				// Ok case, value found so set value to zero
				$result[$k] = '0';
			}
			else
			{
				// Error case, value NOT found so set value to one
				$result[$k] = '1';
			}
		}

		// If there's a one it will return true and test fails
		$this->assertFalse(in_array('1', $result));
	}

	/**
	 * Tests the JDatabasePostgresql getVersion method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetVersion()
	{
		$versionRow = self::$driver->setQuery('SELECT version();')->loadRow();
		preg_match('/((\d+)\.)((\d+)\.)(\*|\d+)/', $versionRow[0], $versionArray);

		$this->assertGreaterThanOrEqual($versionArray[0], self::$driver->getVersion());
	}

	/**
	 * Tests the JDatabasePostgresql insertId method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInsertid()
	{
		self::$driver->setQuery('TRUNCATE TABLE "jos_dbtest"');
		self::$driver->execute();

		/* increment the sequence automatically with INSERT INTO,
		 * first insert to have a common starting point */
		$query = self::$driver->getQuery(true);
		$query->insert('jos_dbtest')
			->columns('title,start_date,description')
			->values("'testTitle','1970-01-01','testDescription'");
		self::$driver->setQuery($query);
		self::$driver->execute();

		/* get the current sequence value */
		$actualVal = self::$driver->getQuery(true);
		$actualVal->select("currval('jos_dbtest_id_seq'::regclass)");
		self::$driver->setQuery($actualVal);
		$idActualVal = self::$driver->loadRow();

		/* insert again, then call insertid() */
		$secondInsertQuery = self::$driver->getQuery(true);
		$secondInsertQuery->insert('jos_dbtest')
			->columns('title,start_date,description')
			->values("'testTitle2nd', '1971-01-01', 'testDescription2nd'");
		self::$driver->setQuery($secondInsertQuery);
		self::$driver->execute();

		/* get insertid of last INSERT INTO */
		$insertId = self::$driver->insertid();

		/* check if first sequence val +1 is equal to last sequence val */
		$this->assertEquals($idActualVal[0] + 1, $insertId);
	}

	/**
	 * Test insertObject function
	 *
	 * @return   void
	 *
	 * @since    12.1
	 */
	public function testInsertObject()
	{
		self::$driver->setQuery('ALTER SEQUENCE jos_dbtest_id_seq RESTART WITH 1');
		self::$driver->execute();

		self::$driver->truncateTable('jos_dbtest');
		self::$driver->execute();

		$tst = new JObject;
		$tst->title = "PostgreSQL test insertObject";
		$tst->start_date = '2012-04-07 15:00:00';
		$tst->description = "Test insertObject";

		// Insert object without retrieving key
		$ret = self::$driver->insertObject('#__dbtest', $tst);

		$checkQuery = self::$driver->getQuery(true);
		$checkQuery->select('COUNT(*)')
			->from('#__dbtest')
			->where('start_date = \'2012-04-07 15:00:00\'', 'AND')
			->where('description = \'Test insertObject\'')
			->where('title = \'PostgreSQL test insertObject\'');
		self::$driver->setQuery($checkQuery);

		$this->assertEquals(1, self::$driver->loadResult());
		$this->assertTrue($ret);

		// Insert object retrieving the key
		$tstK = new JObject;
		$tstK->title = "PostgreSQL test insertObject with key";
		$tstK->start_date = '2012-04-07 15:00:00';
		$tstK->description = "Test insertObject with key";
		$retK = self::$driver->insertObject('#__dbtest', $tstK, 'id');

		$this->assertEquals(2, $tstK->id);
		$this->assertTrue($retK);
	}

	/**
	 * Test isSupported function.
	 *
	 * @return   void
	 */
	public function testIsSupported()
	{
		$this->assertTrue(JDatabaseDriverPostgresql::isSupported());
	}

	/**
	 * Test loadAssoc method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadAssoc()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssoc();

		$this->assertEquals(array('title' => 'Testing'), $result);
	}

	/**
	 * Test loadAssocList method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadAssocList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssocList();

		$this->assertEquals(
			array(array('title' => 'Testing'), array('title' => 'Testing2'), array('title' => 'Testing3'), array('title' => 'Testing4')),
			$result
		);
	}

	/**
	 * Test loadColumn method
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadColumn()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadColumn();

		$this->assertEquals(array('Testing', 'Testing2', 'Testing3', 'Testing4'), $result);
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
	 * @since   11.3
	 */
	public function testLoadObject()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadObject();

		$objCompare = new stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$this->assertEquals($objCompare, $result);
	}

	/**
	 * Test loadObjectList method
	 *
	 * @return  void
	 *
	 * @since   11.3
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

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test loadResult method
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadResult()
	{
		$query = self::$driver->getQuery(true);
		$query->select('id');
		$query->from('#__dbtest');
		$query->where('title=' . self::$driver->quote('Testing2'));

		self::$driver->setQuery($query);
		$result = self::$driver->loadResult();

		$this->assertEquals(2, $result);
	}

	/**
	 * Test loadRow method
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadRow()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRow();

		$this->assertEquals(array(3, 'Testing3', '1980-04-18 00:00:00', 'three'), $result);
	}

	/**
	 * Test loadRowList method
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadRowList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRowList();

		$this->assertEquals(array(array(1, 'Testing', '1980-04-18 00:00:00', 'one'), array(2, 'Testing2', '1980-04-18 00:00:00', 'one')), $result);
	}

	/**
	 * Test the JDatabasePostgresql::execute() method
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecute()
	{
		$query = self::$driver->getQuery(true);
		$query->insert('#__dbtest')
			->columns('id,title,start_date, description')
			->values("5, 'testTitle','1970-01-01','testDescription'")
			->returning('id');

		self::$driver->setQuery($query);

		$this->assertEquals(5, self::$driver->loadResult());
	}

	/**
	 * Test quoteName function, with and without dot notation.
	 *
	 * @param   string  $quoteMe   String to be quoted
	 * @param   string  $asPart    String used for AS query part
	 * @param   string  $expected  Expected string
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider dataTestQuoteName
	 */
	public function testQuoteName($quoteMe, $asPart, $expected)
	{
		$this->assertEquals($expected, self::$driver->quoteName($quoteMe, $asPart));
	}

	/**
	 * Tests the JDatabasePostgresql select method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSelect()
	{
		/* it's not possible to select a database, already done during connection, return true */
		$this->assertTrue(self::$driver->select('database'));
	}

	/**
	 * Tests the JDatabasePostgresql sqlValue method.
	 *
	 * @return  void
	 *
	 * @since 12.2
	 */
	public function testSqlValue()
	{
		// Array of columns' description as that returned by getTableColumns
		$tablCol = array(
			'id' => 'integer',
			'charVar' => 'character varying',
			'timeStamp' => 'timestamp without time zone',
			'nullDate' => 'timestamp without time zone',
			'txt' => 'text',
			'boolTrue' => 'boolean',
			'boolFalse' => 'boolean',
			'num' => 'numeric,',
			'nullInt' => 'integer'
		);

		$values = array();

		// Object containing fields of integer, character varying, timestamp and text type
		$tst = new JObject;
		$tst->id = '5';
		$tst->charVar = "PostgreSQL test insertObject";
		$tst->timeStamp = '2012-04-07 15:00:00';
		$tst->nullDate = null;
		$tst->txt = "Test insertObject";
		$tst->boolTrue = 't';
		$tst->boolFalse = 'f';
		$tst->num = '43.2';
		$tst->nullInt = '';

		foreach (get_object_vars($tst) as $key => $val)
		{
			$values[] = self::$driver->sqlValue($tablCol, $key, $val);
		}

		$this->assertEquals(
			"5,'PostgreSQL test insertObject','2012-04-07 15:00:00','1970-01-01 00:00:00','Test insertObject',TRUE,FALSE,43.2,NULL",
			implode(',', $values)
		);
	}

	/**
	 * Test setUTF function
	 *
	 * @return   void
	 */
	public function testSetUtf()
	{
		if (!function_exists('pg_set_client_encoding'))
		{
			$this->assertEquals(-1, self::$driver->setUtf());
		}
		else
		{
			$this->assertEquals(0, self::$driver->setUtf());
		}
	}

	/**
	 * Test Test method - there really isn't a lot to test here, but
	 * this is present for the sake of completeness
	 *
	 * @return   void
	 */
	public function testTest()
	{
		$this->assertTrue(JDatabaseDriverPostgresql::test());
	}

	/**
	 * Tests the JDatabasePostgresql transactionCommit method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTransactionCommit()
	{
		self::$driver->transactionStart();
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id,title,start_date,description')
			->values("6, 'testTitle','1970-01-01','testDescription'");

		self::$driver->setQuery($queryIns)->execute();

		self::$driver->transactionCommit();

		/* check if value is present */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where('id=6');
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRow();

		$expected = array(6, 'testTitle', '1970-01-01 00:00:00', 'testDescription');

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the JDatabasePostgresql transactionRollback method,
	 * with and without savepoint.
	 *
	 * @param   string  $toSavepoint  Savepoint name to rollback transaction to
	 * @param   int     $tupleCount   Number of tuple found after insertion and rollback
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider dataTestTransactionRollback
	 */
	public function testTransactionRollback($toSavepoint, $tupleCount)
	{
		self::$driver->transactionStart();

		/* try to insert this tuple, inserted only when savepoint != null */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id,title,start_date,description')
			->values("7, 'testRollback','1970-01-01','testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		/* create savepoint only if is passed by data provider */
		if (!is_null($toSavepoint))
		{
			self::$driver->transactionStart((boolean) $toSavepoint);
		}

		/* try to insert this tuple, always rolled back */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id,title,start_date,description')
			->values("8, 'testRollback','1972-01-01','testRollbackSp'");
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
			->where("description='testRollbackSp'");
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRowList();

		$this->assertCount($tupleCount, $result);
	}

	/**
	 * Tests the JDatabasePostgresql transactionStart method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTransactionStart()
	{
		self::$driver->transactionRollback();
		self::$driver->transactionStart();
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id,title,start_date,description')
			->values("6, 'testTitle','1970-01-01','testDescription'");

		self::$driver->setQuery($queryIns)->execute();

		/* check if is present an exclusive lock, it means a transaction is running */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('pg_catalog.pg_locks')
			->where('transactionid NOTNULL');
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadAssocList();

		$this->assertCount(1, $result);
	}

	/**
	 * Test for release of transaction savepoint, correct case is already tested inside
	 * 		testTransactionRollback, here will be tested a RELEASE SAVEPOINT of an
	 * 		inexistent savepoint that will throw and exception.
	 *
	 * @return  void
	 *
	 * @expectedException RuntimeException
	 */
	public function testReleaseTransactionSavepoint()
	{
		self::$driver->transactionRollback();
		self::$driver->transactionStart();

		/* release a nonexistent savepoint will throw an exception */
		try
		{
			self::$driver->releaseTransactionSavepoint('pippo');
		}
		catch (RuntimeException $e)
		{
			self::$driver->transactionRollback();
			throw $e;
		}
	}

	/**
	 * Tests the JDatabasePostgresql renameTable method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRenameTable()
	{
		$newTableName = 'bak_jos_dbtest';

		self::$driver->renameTable('jos_dbtest', $newTableName);

		/* check name change */
		$tableList = self::$driver->getTableList();
		$this->assertTrue(in_array($newTableName, $tableList));

		/* check index change */
		self::$driver->setQuery(
			'SELECT relname
							FROM pg_class
							WHERE oid IN (
								SELECT indexrelid
								FROM pg_index, pg_class
								WHERE pg_class.relname=\'' . $newTableName . '\' AND pg_class.oid=pg_index.indrelid );');

		$oldIndexes = self::$driver->loadColumn();
		$this->assertEquals('bak_jos_dbtest_pkey', $oldIndexes[0]);

		/* check sequence change */
		self::$driver->setQuery(
			'SELECT relname
							FROM pg_class
							WHERE relkind = \'S\'
							AND relnamespace IN (
								SELECT oid
								FROM pg_namespace
								WHERE nspname NOT LIKE \'pg_%\'
								AND nspname != \'information_schema\'
							)
							AND relname LIKE \'%' . $newTableName . '%\' ;');

		$oldSequences = self::$driver->loadColumn();
		$this->assertEquals('bak_jos_dbtest_id_seq', $oldSequences[0]);

		/* restore initial state */
		self::$driver->renameTable($newTableName, 'jos_dbtest');
	}

	/**
	 * Tests the JDatabasePostgresql replacePrefix method.
	 *
	 * @param   text  $stringToReplace  The string in which replace the prefix.
	 * @param   text  $prefix           The prefix.
	 * @param   text  $expected         The string expected.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider  dataTestReplacePrefix
	 */
	public function testReplacePrefix($stringToReplace, $prefix, $expected)
	{
		$result = self::$driver->replacePrefix($stringToReplace, $prefix);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the JDatabasePostgresql getCreateDbQuery method.
	 *
	 * @param   JObject  $options  JObject coming from "initialise" function to pass user
	 * 									and database name to database driver.
	 * @param   boolean  $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  void
	 *
	 * @dataProvider dataGetCreateDbQuery
	 */
	public function testGetCreateDbQuery($options, $utf)
	{
		$expected = 'CREATE DATABASE ' . self::$driver->quoteName($options->db_name) . ' OWNER ' . self::$driver->quoteName($options->db_user);

		if ($utf)
		{
			$expected .= ' ENCODING ' . self::$driver->quote('UTF-8');
		}

		$result = self::$driver->getCreateDbQuery($options, $utf);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the JDatabasePostgresql getAlterDbCharacterSet method.
	 *
	 * @return  void
	 */
	public function testGetAlterDbCharacterSet()
	{
		$expected = 'ALTER DATABASE ' . self::$driver->quoteName('test') . ' SET CLIENT_ENCODING TO ' . self::$driver->quote('UTF8');

		$result = self::$driver->getAlterDbCharacterSet('test');

		$this->assertEquals($expected, $result);
	}
}
