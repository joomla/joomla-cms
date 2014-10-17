<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test the JDatabaseExporterPostgresql class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       12.1
 */
class JDatabaseExporterPostgresqlTest extends TestCase
{
	/**
	 * @var    JDatabaseDriverPostgresql  The mocked database object for use by test methods.
	 * @since  12.1
	 */
	protected $dbo = null;

	/**
	 * @var    string  A query string or object.
	 * @since  12.1
	 */
	protected $lastQuery = null;

	/**
	 * @var    bool  Boolean value to know if current database version is newer than 9.1.0
	 * @since  12.1
	 */
	private $_ver9dot1 = true;

	/**
	 * Sets up the testing conditions
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setup()
	{
		parent::setUp();

		// Set up the database object mock.
		$this->dbo = $this->getMockDatabase('Postgresql', array('getTableSequences'), '1970-01-01 00:00:00', 'Y-m-d H:i:s');

		$this->dbo->expects($this->any())
			->method('getPrefix')
			->willReturn('jos_');

		$this->dbo->expects($this->any())
			->method('getTableColumns')
			->willReturn(
				array(
					(object) array(
						'column_name' => 'id',
						'type' => 'integer',
						'null' => 'NO',
						'default' => 'nextval(\'jos_dbtest_id_seq\'::regclass)',
						'comments' => '',
					),
					(object) array(
						'column_name' => 'title',
						'type' => 'character varying(50)',
						'null' => 'NO',
						'default' => 'NULL',
						'comments' => '',
					),
					(object) array(
						'column_name' => 'start_date',
						'type' => 'timestamp without time zone',
						'null' => 'NO',
						'default' => 'NULL',
						'comments' => '',
					),
					(object) array(
						'column_name' => 'description',
						'type' => 'text',
						'null' => 'NO',
						'default' => 'NULL',
						'comments' => '',
					)
				)
			);

		$this->dbo->expects($this->any())
			->method('getTableKeys')
			->willReturn(array(
				(object) array(
					'idxName' => 'jos_dbtest_pkey',
					'isPrimary' => 'TRUE',
					'isUnique' => 'TRUE',
					'Query' => 'ALTER TABLE "jos_dbtest" ADD PRIMARY KEY (id)',
				)
			));

		// Check if database is at least 9.1.0
		$this->dbo->expects($this->any())
			->method('getVersion')
			->willReturn('9.1.2');

		if (version_compare($this->dbo->getVersion(), '9.1.0') >= 0)
		{
			$this->_ver9dot1 = true;
			$start_val = '1';
		}
		else
		{
			/* Older version */
			$this->_ver9dot1 = false;
			$start_val = null;
		}

		$this->dbo->expects($this->any())
			->method('getTableSequences')
			->willReturn(
				array(
					(object) array(
						'sequence' => 'jos_dbtest_id_seq',
						'schema' => 'public',
						'table' => 'jos_dbtest',
						'column' => 'id',
						'data_type' => 'bigint',
						'start_value' => $start_val,
						'minimum_value' => '1',
						'maximum_value' => '9223372036854775807',
						'increment' => '1',
						'cycle_option' => 'NO',
					)
				)
			);

		$this->dbo->expects($this->any())
			->method('loadObjectList')
			->willReturn(array());

		$this->dbo->expects($this->any())
			->method('getTableList')
			->willReturn(array('jos_dbtest'));
	}

	/**
	 * Mock quoteName method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in MySQL quotes.
	 *
	 * @since   3.4
	 */
	public function mockQuoteName($value)
	{
		return '"$value"';
	}

	/**
	 * Callback for the dbo getQuery method.
	 *
	 * @param   boolean  $new  True to get a new query, false to get the last query.
	 *
	 * @return  JDatabaseQueryPostgresql
	 *
	 * @since   11.3
	 */
	public function mockGetQuery($new = false)
	{
		if ($new)
		{
			return new JDatabaseQueryPostgresql($this->dbo);
		}
		else
		{
			return $this->$lastQuery;
		}
	}

	/**
	 * Test the magic __toString method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toString()
	{
		$instance = new JDatabaseExporterPostgresql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true);

		/* Depending on which version is running, 9.1.0 or older */
		$start_val = null;

		if ($this->_ver9dot1)
		{
			$start_val = '1';
		}

		$expecting = '<?xml version="1.0"?>
<postgresqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <sequence Name="jos_dbtest_id_seq" Schema="public" Table="jos_dbtest" Column="id" Type="bigint" Start_Value="' .
			$start_val . '" Min_Value="1" Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" />
   <field Field="id" Type="integer" Null="NO" Default="nextval(\'jos_dbtest_id_seq\'::regclass)" Comments="" />
   <field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />
   <field Field="start_date" Type="timestamp without time zone" Null="NO" Default="NULL" Comments="" />
   <field Field="description" Type="text" Null="NO" Default="NULL" Comments="" />
   <key Index="jos_dbtest_pkey" is_primary="TRUE" is_unique="TRUE" Query="ALTER TABLE "jos_dbtest" ADD PRIMARY KEY (id)" />
  </table_structure>
 </database>
</postgresqldump>';

		$this->assertSame(
			preg_replace('/\v/', '', $expecting),
			preg_replace('/\v/', '', (string) $instance)
		);
	}

	/**
	 * Tests the asXml method.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testAsXml()
	{
		$instance = new JDatabaseExporterPostgresql;

		$result = $instance->asXml();

		$this->assertSame(
			$instance,
			$result,
			'asXml must return an object to support chaining.'
		);

		$this->assertAttributeEquals(
			'xml',
			'asFormat',
			$instance,
			'The asXml method should set the protected asFormat property to "xml".'
		);
	}

	/**
	 * Test the buildXML method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testBuildXml()
	{
		$instance = new JDatabaseExporterPostgresql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true);

		/* Depending on which version is running, 9.1.0 or older */
		$start_val = null;

		if ($this->_ver9dot1)
		{
			$start_val = '1';
		}

		$expecting = '<?xml version="1.0"?>
<postgresqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <sequence Name="jos_dbtest_id_seq" Schema="public" Table="jos_dbtest" Column="id" Type="bigint" Start_Value="' .
			$start_val . '" Min_Value="1" Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" />
   <field Field="id" Type="integer" Null="NO" Default="nextval(\'jos_dbtest_id_seq\'::regclass)" Comments="" />
   <field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />
   <field Field="start_date" Type="timestamp without time zone" Null="NO" Default="NULL" Comments="" />
   <field Field="description" Type="text" Null="NO" Default="NULL" Comments="" />
   <key Index="jos_dbtest_pkey" is_primary="TRUE" is_unique="TRUE" Query="ALTER TABLE "jos_dbtest" ADD PRIMARY KEY (id)" />
  </table_structure>
 </database>
</postgresqldump>';

		// Replace used to prevent platform conflicts
		$this->assertSame(
			preg_replace('/\v/', '', $expecting),
			preg_replace('/\v/', '', TestReflection::invoke($instance, 'buildXml'))
		);
	}

	/**
	 * Tests the buildXmlStructure method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testBuildXmlStructure()
	{
		$instance = new JDatabaseExporterPostgresql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true);

		/* Depending on which version is running, 9.1.0 or older */
		$start_val = null;

		if ($this->_ver9dot1)
		{
			$start_val = '1';
		}

		$this->assertEquals(
			array(
				'  <table_structure name="#__test">',
				'   <sequence Name="jos_dbtest_id_seq" Schema="public" Table="jos_dbtest" Column="id" Type="bigint" Start_Value="' .
				$start_val . '" Min_Value="1" Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" />',
				'   <field Field="id" Type="integer" Null="NO" Default="nextval(\'jos_dbtest_id_seq\'::regclass)" Comments="" />',
				'   <field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />',
				'   <field Field="start_date" Type="timestamp without time zone" Null="NO" Default="NULL" Comments="" />',
				'   <field Field="description" Type="text" Null="NO" Default="NULL" Comments="" />',
				'   <key Index="jos_dbtest_pkey" is_primary="TRUE" is_unique="TRUE" Query="ALTER TABLE "jos_dbtest" ADD PRIMARY KEY (id)" />',
				'  </table_structure>'
			),
			TestReflection::invoke($instance, 'buildXmlStructure')
		);
	}

	/**
	 * Tests the check method.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testCheckWithNoDbo()
	{
		$instance = new JDatabaseExporterPostgresql;

		try
		{
			$instance->check();
		}
		catch (Exception $e)
		{
			// Exception expected.
			return;
		}

		$this->fail('Check method should throw exception if DBO not set');
	}

	/**
	 * Tests the check method.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testCheckWithNoTables()
	{
		$instance	= new JDatabaseExporterPostgresql;
		$instance->setDbo($this->dbo);

		try
		{
			$instance->check();
		}
		catch (Exception $e)
		{
			// Exception expected.
			return;
		}

		$this->fail('Check method should throw exception if the from property not set');
	}

	/**
	 * Tests the check method.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testCheckWithGoodInput()
	{
		$instance	= new JDatabaseExporterPostgresql;
		$instance->setDbo($this->dbo);
		$instance->from('foobar');

		try
		{
			$result = $instance->check();

			$this->assertSame(
				$instance,
				$result
			);
		}
		catch (Exception $e)
		{
			$this->fail('Check method should not throw exception with good setup: ' . $e->getMessage());
		}
	}

	/**
	 * Tests the from method with bad input.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testFromWithBadInput()
	{
		$instance = new JDatabaseExporterPostgresql;

		try
		{
			$instance->from(new stdClass);
		}
		catch (Exception $e)
		{
			// Exception expected.
			return;
		}

		$this->fail('From method should thrown an exception if argument is not a string or array.');
	}

	/**
	 * Tests the from method with expected good inputs.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseExporterPostgresql;

		try
		{
			$result = $instance->from('jos_foobar');

			$this->assertSame(
				$instance,
				$result,
				'from must return an object to support chaining.'
			);

			$this->assertAttributeEquals(
				array('jos_foobar'),
				'from',
				$instance,
				'The from method should convert a string input to an array.'
			);
		}
		catch (Exception $e)
		{
			$this->fail('From method should not throw exception with good input: ' . $e->getMessage());
		}
	}

	/**
	 * Tests the method getGenericTableName method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetGenericTableName()
	{
		$instance = new JDatabaseExporterPostgresql;
		$instance->setDbo($this->dbo);

		$this->assertSame(
			'#__test',
			TestReflection::invoke($instance, 'getGenericTableName', 'jos_test'),
			'The testGetGenericTableName should replace the database prefix with #__.'
		);
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testSetDboWithBadInput()
	{
		$instance = new JDatabaseExporterPostgresql;

		try
		{
			$instance->setDbo(new stdClass);
		}
		catch (PHPUnit_Framework_Error $e)
		{
			// Expecting the error, so just ignore it.
			return;
		}

		$this->fail('setDbo requires a JDatabasePostgresql object and should throw an exception.');
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testSetDboWithGoodInput()
	{
		$instance = new JDatabaseExporterPostgresql;

		try
		{
			$result = $instance->setDbo($this->dbo);

			$this->assertSame(
				$instance,
				$result,
				'setDbo must return an object to support chaining.'
			);
		}
		catch (PHPUnit_Framework_Error $e)
		{
			// Unknown error has occurred.
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Tests the withStructure method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testWithStructure()
	{
		$instance = new JDatabaseExporterPostgresql;

		$result = $instance->withStructure();

		$this->assertSame(
			$instance,
			$result,
			'withStructure must return an object to support chaining.'
		);

		$options = TestReflection::getValue($instance, 'options');

		$this->assertTrue(
			$options->withStructure,
			'The default use of withStructure should result in true.'
		);

		$instance->withStructure(true);

		$options = TestReflection::getValue($instance, 'options');

		$this->assertTrue(
			$options->withStructure,
			'The explicit use of withStructure with true should result in true.'
		);

		$instance->withStructure(false);

		$options = TestReflection::getValue($instance, 'options');

		$this->assertFalse(
			$options->withStructure,
			'The explicit use of withStructure with false should result in false.'
		);
	}
}
