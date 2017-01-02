<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test the JDatabaseExporterPostgresql class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 */
class JDatabaseExporterPostgresqlTest extends TestCase
{
	/**
	 * @var    JDatabaseDriverPostgresql  The mocked database object for use by test methods.
	 */
	protected $dbo;

	/**
	 * @var    string  A query string or object.
	 */
	protected $lastQuery = null;

	/**
	 * @var    bool  Boolean value to know if current database version is newer than 9.1.0
	 */
	private $_ver9dot1 = true;

	/**
	 * Sets up the testing conditions
	 *
	 * @return  void
	 */
	protected function setup()
	{
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
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->dbo);
	}

	/**
	 * Mock quoteName method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in MySQL quotes.
	 */
	public function mockQuoteName($value)
	{
		return "'$value'";
	}

	/**
	 * Callback for the dbo getQuery method.
	 *
	 * @param   boolean  $new  True to get a new query, false to get the last query.
	 *
	 * @return  JDatabaseQueryPostgresql
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
	 * @expectedException Exception
	 */
	public function testCheckWithNoDbo()
	{
		$instance = new JDatabaseExporterPostgresql;

		$instance->check();
	}

	/**
	 * Tests the check method.
	 *
	 * @expectedException Exception
	 */
	public function testCheckWithNoTables()
	{
		$instance	= new JDatabaseExporterPostgresql;
		$instance->setDbo($this->dbo);

		$instance->check();
	}

	/**
	 * Tests the check method.
	 */
	public function testCheckWithGoodInput()
	{
		$instance	= new JDatabaseExporterPostgresql;
		$instance->setDbo($this->dbo);
		$instance->from('foobar');

		$result = $instance->check();

		$this->assertSame(
			$instance,
			$result
		);
	}

	/**
	 * Tests the from method with bad input.
	 *
	 * @expectedException Exception
	 */
	public function testFromWithBadInput()
	{
		$instance = new JDatabaseExporterPostgresql;

		$instance->from(new stdClass);
	}

	/**
	 * Tests the from method with expected good inputs.
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseExporterPostgresql;

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

	/**
	 * Tests the method getGenericTableName method.
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
	 */
	public function testSetDboWithGoodInput()
	{
		$instance = new JDatabaseExporterPostgresql;

		$result = $instance->setDbo($this->dbo);

		$this->assertSame(
			$instance,
			$result,
			'setDbo must return an object to support chaining.'
		);
	}

	/**
	 * Tests the withStructure method.
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
