<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/JDatabaseImporterPostgresqlInspector.php';

/**
 * Test the JDatabaseImporterPostgresql class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @since       12.1
 */
class JDatabaseImporterPostgresqlTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    object  The mocked database object for use by test methods.
	 * @since  12.1
	 */
	protected $dbo = null;

	/**
	 * @var    string  The last query sent to the dbo setQuery method.
	 * @since  12.1
	 */
	protected $lastQuery = '';

	/**
	 * Sets up the testing conditions
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function setup()
	{
		parent::setUp();

		// Set up the database object mock.
		$this->dbo = $this->getMock(
			'JDatabaseDriverPostgresql',
			array(
				'getErrorNum',
				'getPrefix',
				'getTableColumns',
				'getTableKeys',
				'getTableSequences',
				'getAddSequenceSQL',
				'getChangeSequenceSQL',
				'getDropSequenceSQL',
				'getAddIndexSQL',
				'getVersion',
				'quoteName',
				'loadObjectList',
				'quote',
				'setQuery',
			),
			array(),
			'',
			false
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('getPrefix')
		->will(
			$this->returnValue(
				'jos_'
			)
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('getTableColumns')
		->will(
			$this->returnValue(
				array(
					'id' => (object) array(
						'Field' => 'id',
						'Type' => 'integer',
						'Null' => 'NO',
						'Default' => 'nextval(\'jos_dbtest_id_seq\'::regclass)',
						'Comments' => '',
					),
					'title' => (object) array(
						'Field' => 'title',
						'Type' => 'character varying(50)',
						'Null' => 'NO',
						'Default' => 'NULL',
						'Comments' => '',
					),
				)
			)
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('getTableKeys')
		->will(
			$this->returnValue(
				array(
					(object) array(
						'Index' => 'jos_dbtest_pkey',
						'is_primary' => 'TRUE',
						'is_unique' => 'TRUE',
						'Query' => 'ALTER TABLE jos_dbtest ADD PRIMARY KEY (id)',
					),
					(object) array(
						'Index' => 'jos_dbtest_idx_name',
						'is_primary' => 'FALSE',
						'is_unique' => 'FALSE',
						'Query' => 'CREATE INDEX jos_dbtest_idx_name ON jos_dbtest USING btree (name)',
					)
				)
			)
		);

		// Check if database is at least 9.1.0
		$this->dbo->expects(
			$this->any()
		)
		->method('getVersion')
		->will(
			$this->returnValue(
				'7.1.2'
			)
		);

		if (version_compare($this->dbo->getVersion(), '9.1.0') >= 0)
		{
			$start_val = '1';
		}
		else
		{
			/* Older version */
			$start_val = null;
		}

		$this->dbo->expects(
			$this->any()
		)
		->method('getTableSequences')
		->will(
			$this->returnValue(
			array(
					(object) array(
						'Name' => 'jos_dbtest_id_seq',
						'Schema' => 'public',
						'Table' => 'jos_dbtest',
						'Column' => 'id',
						'Type' => 'bigint',
						'Start_Value' => $start_val,
						'Min_Value' => '1',
						'Max_Value' => '9223372036854775807',
						'Increment' => '1',
						'Cycle_option' => 'NO',
					)
				)
			)
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('quoteName')
		->will(
			$this->returnCallback(
				array($this, 'callbackQuoteName')
			)
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('quote')
		->will(
			$this->returnCallback(
				array($this, 'callbackQuote')
			)
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('setQuery')
		->will(
			$this->returnCallback(
				array($this, 'callbackSetQuery')
			)
		);

		$this->dbo->expects(
			$this->any()
		)
		->method('loadObjectList')
		->will(
			$this->returnCallback(
				array($this, 'callbackLoadObjectList')
			)
		);
	}

	/**
	 * Callback for the dbo loadObjectList method.
	 *
	 * @return array  An array of results based on the setting of the last query.
	 *
	 * @since  12.1
	 */
	public function callbackLoadObjectList()
	{
		return array('');
	}

	/**
	 * Callback for the dbo quote method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in MySQL quotes.
	 *
	 * @since  12.1
	 */
	public function callbackQuote($value)
	{
		return "'$value'";
	}

	/**
	 * Callback for the dbo quoteName method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in MySQL quotes.
	 *
	 * @since  12.1
	 */
	public function callbackQuoteName($value)
	{
		return "\"$value\"";
	}

	/**
	 * Callback for the dbo setQuery method.
	 *
	 * @param   string  $query  The query.
	 *
	 * @return  void
	 *
	 * @since  12.1
	 */
	public function callbackSetQuery($query)
	{
		$this->lastQuery = $query;
	}

	/**
	 * Data for the testGetAlterTableSQL test.
	 *
	 * @return  array  Each array element must be an array with 3 elements: SimpleXMLElement field, expected result, error message.
	 *
	 * @since   12.1
	 */
	public function dataGetAlterTableSQL()
	{
		$f1 = '<field Field="id" Type="integer" Null="NO" Default="nextval(\'jos_dbtest_id_seq\'::regclass)" ' .
			'Comments="" />';
		$f2 = '<field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />';
		$f3 = '<field Field="alias" Type="character varying(255)" Null="NO" Default="test" Comments="" />';
		$f2_def = '<field Field="title" Type="character varying(50)" Null="NO" Default="add default" Comments="" />';

		$k1 = '<key Index="jos_dbtest_pkey" is_primary="TRUE" is_unique="TRUE" Query="ALTER TABLE jos_dbtest ADD PRIMARY KEY (id)" />';
		$k2 = '<key Index="jos_dbtest_idx_name" is_primary="FALSE" is_unique="FALSE" Query="CREATE INDEX jos_dbtest_idx_name ON' .
			' jos_dbtest USING btree (name)" />';
		$k3 = '<key Index="jos_dbtest_idx_title" is_primary="FALSE" is_unique="FALSE" Query="CREATE INDEX ' .
			'jos_dbtest_idx_title ON jos_dbtest USING btree (title)" />';
		$k4 = '<key Index="jos_dbtest_uidx_name" is_primary="FALSE" is_unique="TRUE" Query="CREATE UNIQUE INDEX ' .
			'jos_dbtest_uidx_name ON jos_dbtest USING btree (name)" />';
		$pk = '<key Index="jos_dbtest_title_pkey" is_primary="TRUE" is_unique="TRUE" ' .
			'Query="ALTER TABLE jos_dbtest ADD PRIMARY KEY (title)" />';

		$s1 = '<sequence Name="jos_dbtest_id_seq" Schema="public" Table="jos_dbtest" Column="id" Type="bigint" Start_Value="1" ' .
			'Min_Value="1" Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" />';
		$s2 = '<sequence Name="jos_dbtest_title_seq" Schema="public" Table="jos_dbtest" Column="title" Type="bigint" Start_Value="1" Min_Value="1" ' .
			'Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" />';

		$addSequence = 'CREATE SEQUENCE jos_dbtest_title_seq INCREMENT BY 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 ' .
			'NO CYCLE OWNED BY "public.jos_dbtest.title"';
		$changeCol = "ALTER TABLE \"jos_test\" ALTER COLUMN \"title\"  TYPE character " .
			"varying(50),\nALTER COLUMN \"title\" SET NOT NULL,\nALTER COLUMN \"title\" SET DEFAULT 'add default'";
		$changeSeq = "CREATE SEQUENCE jos_dbtest_title_seq INCREMENT BY 1 MINVALUE 1 MAXVALUE 9223372036854775807 " .
			"START 1 NO CYCLE OWNED BY \"public.jos_dbtest.title\"";

		return array(
			array(
				new SimpleXmlElement('<table_structure name="#__dbtest">' . $s1 . $f1 . $f2 . $k1 . $k2 . '</table_structure>'),
				array(
				),
				'getAlterTableSQL should not change anything.'
			),
			array(
				// Add col
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $f3 . $k1 . $k2 . '</table_structure>'),
				array(
					'ALTER TABLE "jos_test" ADD COLUMN "alias" character varying(255) NOT NULL DEFAULT \'test\'',
				),
				'getAlterTableSQL should add the new alias column.'
			),
			array(
				// Add idx
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $k1 . $k2 . $k3 . '</table_structure>'),
				array('CREATE INDEX jos_dbtest_idx_title ON jos_dbtest USING btree (title)',),
				'getAlterTableSQL should add the new key.'
			),
			array(
				// Add unique idx
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $k1 . $k2 . $k4 . '</table_structure>'),
				array(
					'CREATE UNIQUE INDEX jos_dbtest_uidx_name ON jos_dbtest USING btree (name)',
				),
				'getAlterTableSQL should add the new unique key.'
			),
			array(
				// Add sequence
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $s2 . $f1 . $f2 . $k1 . $k2 . '</table_structure>'),
				array(
					$addSequence,
				),
				'getAlterTableSQL should add the new sequence.'
			),
			array(
				// Add pkey
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $k1 . $k2 . $pk . '</table_structure>'),
				array(
					'ALTER TABLE jos_dbtest ADD PRIMARY KEY (title)',
				),
				'getAlterTableSQL should add the new sequence.'
			),
			array(
				// Drop col
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $k1 . $k2 . '</table_structure>'),
				array(
					'ALTER TABLE "jos_test" DROP COLUMN "title"',
				),
				'getAlterTableSQL should remove the title column.'
			),
			array(
				// Drop idx
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $k1 . '</table_structure>'),
				array(
					"DROP INDEX \"jos_dbtest_idx_name\""
				),
				'getAlterTableSQL should change sequence.'
			),
			array(
				// Drop seq
				new SimpleXmlElement('<table_structure name="#__test">' . $f1 . $f2 . $k1 . $k2 . '</table_structure>'),
				array(
					'DROP SEQUENCE "jos_dbtest_id_seq"',
				),
				'getAlterTableSQL should drop the sequence.'
			),
			array(
			// Drop pkey
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $k2 . '</table_structure>'),
				array(
					'ALTER TABLE ONLY "jos_test" DROP CONSTRAINT "jos_dbtest_pkey"',
				),
				'getAlterTableSQL should drop the old primary key.'
			),
			array(
				// Change col
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2_def . $k1 . $k2 . '</table_structure>'),
				array($changeCol,),
				'getAlterTableSQL should change title field.'
			),
			array(
				// Change seq
				new SimpleXmlElement('<table_structure name="#__test">' . $s2 . $f1 . $f2 . $k1 . $k2 . '</table_structure>'),
				array(
					$changeSeq,
					"DROP SEQUENCE \"jos_dbtest_id_seq\"",),
				'getAlterTableSQL should change sequence.'
			),
			array(
				// Change idx
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $k1 . $k3 . '</table_structure>'),
				array(
					"CREATE INDEX jos_dbtest_idx_title ON jos_dbtest USING btree (title)",
					'DROP INDEX "jos_dbtest_idx_name"'
				),
				'getAlterTableSQL should change index.'
			),
			array(
				// Change pkey
				new SimpleXmlElement('<table_structure name="#__test">' . $s1 . $f1 . $f2 . $pk . $k2 . '</table_structure>'),
				array(
					'ALTER TABLE jos_dbtest ADD PRIMARY KEY (title)',
					'ALTER TABLE ONLY "jos_test" DROP CONSTRAINT "jos_dbtest_pkey"'
				),
				'getAlterTableSQL should change primary key.'
			),
		);
	}

	/**
	 * Data for the testGetColumnSQL test.
	 *
	 * @return  array  Each array element must be an array with 3 elements: SimpleXMLElement field, expected result, error message.
	 *
	 * @since   12.1
	 */
	public function dataGetColumnSQL()
	{
		$sample = array(
			'xml-id-field' => '<field Field="id" Type="integer" Null="NO" Default="nextval(\'jos_dbtest_id_seq\'::regclass)" Comments="" />',
			'xml-title-field' => '<field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />',
			'xml-title-def' => '<field Field="title" Type="character varying(50)" Null="NO" Default="this is a test" Comments="" />',
			'xml-body-field' => '<field Field="description" Type="text" Null="NO" Default="NULL" Comments="" />',);

		return array(
			array(
				new SimpleXmlElement(
					$sample['xml-id-field']
				),
				'"id" serial',
				'Typical primary key field',
			),
			array(
				new SimpleXmlElement(
					$sample['xml-title-field']
				),
				'"title" character varying(50) NOT NULL',
				'Typical text field',
			),
			array(
				new SimpleXmlElement(
					$sample['xml-body-field']
				),
				'"description" text NOT NULL',
				'Typical blob field',
			),
			array(
				new SimpleXmlElement(
					$sample['xml-title-def']
				),
				'"title" character varying(50) NOT NULL DEFAULT \'this is a test\'',
				'Typical text field with default value',
			),
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
		$instance = new JDatabaseImporterPostgresqlInspector;

		$result = $instance->asXml();

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'asXml must return an object to support chaining.'
		);

		$this->assertThat(
			$instance->asFormat,
			$this->equalTo('xml'),
			'The asXml method should set the protected asFormat property to "xml".'
		);
	}

	/**
	 * Tests the check method
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testCheckWithNoDbo()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;

		try
		{
			$instance->check();
		}
		catch (Exception $e)
		{
			// Exception expected.
			return;
		}

		$this->fail(
			'Check method should throw exception if DBO not set'
		);
	}

	/**
	 * Tests the check method.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testCheckWithNoFrom()
	{
		$instance	= new JDatabaseImporterPostgresqlInspector;
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

		$this->fail(
			'Check method should throw exception if DBO not set'
		);
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
		$instance	= new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);
		$instance->from('foobar');

		try
		{
			$result = $instance->check();

			$this->assertThat(
				$result,
				$this->identicalTo($instance),
				'check must return an object to support chaining.'
			);
		}
		catch (Exception $e)
		{
			$this->fail(
				'Check method should not throw exception with good setup: ' . $e->getMessage()
			);
		}
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
		$instance = new JDatabaseImporterPostgresqlInspector;

		try
		{
			$result = $instance->from('foobar');

			$this->assertThat(
				$result,
				$this->identicalTo($instance),
				'from must return an object to support chaining.'
			);

			$this->assertThat(
				$instance->from,
				$this->equalTo('foobar'),
				'The from method did not store the value as expected.'
			);
		}
		catch (Exception $e)
		{
			$this->fail(
				'From method should not throw exception with good input: ' . $e->getMessage()
			);
		}
	}

	/**
	 * Tests the getAddColumnSQL method.
	 *
	 * Note that combinations of fields are tested in testGetColumnSQL.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetAddColumnSQL()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$sample = array(
			'xml-title-field' => '<field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />',
			'xml-title-def' => '<field Field="title" Type="character varying(50)" Null="NO" Default="this is a test" Comments="" />',
			'xml-int-defnum' => '<field Field="title" Type="integer" Null="NO" Default="0" Comments="" />',);

		$this->assertThat(
			$instance->getAddColumnSQL(
				'jos_test',
				new SimpleXmlElement($sample['xml-title-field'])
			),
			$this->equalTo(
				'ALTER TABLE "jos_test" ADD COLUMN "title" character varying(50) NOT NULL'
			),
			'testGetAddColumnSQL did not yield the expected result.'
		);

		// Test a field with a default value
		$this->assertThat(
			$instance->getAddColumnSQL(
				'jos_test',
				new SimpleXmlElement($sample['xml-title-def'])
			),
			$this->equalTo(
				'ALTER TABLE "jos_test" ADD COLUMN "title" character varying(50) NOT NULL DEFAULT \'this is a test\''
			),
			'testGetAddColumnSQL did not yield the expected result.'
		);

		// Test a field with a numeric default value
		$this->assertThat(
			$instance->getAddColumnSQL(
				'jos_test',
				new SimpleXmlElement($sample['xml-int-defnum'])
			),
			$this->equalTo(
				'ALTER TABLE "jos_test" ADD COLUMN "title" integer NOT NULL DEFAULT 0'
			),
			'testGetAddColumnSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getAddSequenceSQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetAddSequenceSQL()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$xmlIdSeq = '<sequence Name="jos_dbtest_id_seq" Schema="public" Table="jos_dbtest" Column="id" ' .
			'Type="bigint" Start_Value="1" Min_Value="1" Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" /> ';

		$this->assertThat(
			$instance->getAddSequenceSQL(
				new SimpleXmlElement($xmlIdSeq)
			),
			$this->equalTo(
				'CREATE SEQUENCE jos_dbtest_id_seq INCREMENT BY 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 NO CYCLE OWNED BY "public.jos_dbtest.id"'
			),
			'getAddSequenceSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getAddIndexSQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetAddIndexSQL()
	{
		$xmlIndex = '<key Index="jos_dbtest_idx_name" is_primary="FALSE" is_unique="FALSE" ' .
			'Query="CREATE INDEX jos_dbtest_idx_name ON jos_dbtest USING btree (name)" />';
		$xmlPrimaryKey = '<key Index="jos_dbtest_pkey" is_primary="TRUE" is_unique="TRUE" ' .
			'Query="ALTER TABLE jos_dbtest ADD PRIMARY KEY (id)" />';

		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getAddIndexSQL(
					new SimpleXmlElement($xmlIndex)
			),
			$this->equalTo(
				"CREATE INDEX jos_dbtest_idx_name ON jos_dbtest USING btree (name)"
			),
			'testGetAddIndexSQL did not yield the expected result.'
		);

		$this->assertThat(
			$instance->getAddIndexSQL(
					new SimpleXmlElement($xmlPrimaryKey)
			),
			$this->equalTo(
				"ALTER TABLE jos_dbtest ADD PRIMARY KEY (id)"
			),
			'testGetAddIndexSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getAlterTableSQL method.
	 *
	 * @param   SimpleXMLElement  $structure  XML structure of field
	 * @param   string            $expected   Expected string
	 * @param   string            $message    Error message
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @dataProvider dataGetAlterTableSQL
	 */
	public function testGetAlterTableSQL($structure, $expected, $message)
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getAlterTableSQL(
				$structure
			),
			$this->equalTo(
				$expected
			),
			$message
		);
	}

	/**
	 * Tests the getChangeColumnSQL method.
	 *
	 * Note that combinations of fields is tested in testGetColumnSQL
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetChangeColumnSQL()
	{
		$xmlTitleField = '<field Field="title" Type="character varying(50)" Null="NO" Default="NULL" Comments="" />';

		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getChangeColumnSQL(
				'jos_test',
				new SimpleXmlElement($xmlTitleField)
			),
			$this->equalTo(
				'ALTER TABLE "jos_test" ALTER COLUMN "title"  TYPE character varying(50),' . "\n" .
				'ALTER COLUMN "title" SET NOT NULL,' . "\n" .
				'ALTER COLUMN "title" DROP DEFAULT'
			),
			'getChangeColumnSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getChangeSequenceSQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetChangeSequenceSQL()
	{
		$xmlIdSeq = '<sequence Name="jos_dbtest_id_seq" Schema="public" Table="jos_dbtest" Column="id" ' .
			'Type="bigint" Start_Value="1" Min_Value="1" Max_Value="9223372036854775807" Increment="1" Cycle_option="NO" /> ';

		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getChangeSequenceSQL(
				new SimpleXmlElement($xmlIdSeq)
			),
			$this->equalTo(
				'ALTER SEQUENCE jos_dbtest_id_seq INCREMENT BY 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 OWNED BY "public.jos_dbtest.id"'
			),
			'getChangeSequenceSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getColumnSQL method.
	 *
	 * @param   SimpleXmlElement  $field     The database field as an object.
	 * @param   string            $expected  The expected result from the getColumnSQL method.
	 * @param   string            $message   The error message to display if the result does not match the expected value.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @dataProvider dataGetColumnSQL
	 */
	public function testGetColumnSQL($field, $expected, $message)
	{
		$instance	= new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			strtolower($instance->getColumnSQL($field)),
			$this->equalTo(strtolower($expected)),
			$message
		);
	}

	/**
	 * Tests the getDropColumnSQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetDropColumnSQL()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getDropColumnSQL(
				'jos_test',
				'title'
			),
			$this->equalTo(
				'ALTER TABLE "jos_test" DROP COLUMN "title"'
			),
			'getDropColumnSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getDropKeySQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetDropIndexSQL()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getDropIndexSQL(
				'idx_title'
			),
			$this->equalTo(
				'DROP INDEX "idx_title"'
			),
			'getDropKeySQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getDropPrimaryKeySQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetDropPrimaryKeySQL()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getDropPrimaryKeySQL(
				'jos_test', 'idx_jos_test_pkey'
			),
			$this->equalTo(
				'ALTER TABLE ONLY "jos_test" DROP CONSTRAINT "idx_jos_test_pkey"'
			),
			'getDropPrimaryKeySQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getDropSequenceSQL method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetDropSequenceSQL()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getDropSequenceSQL(
				'idx_jos_test_seq'
			),
			$this->equalTo(
				'DROP SEQUENCE "idx_jos_test_seq"'
			),
			'getDropSequenceSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getIdxLookup method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetIdxLookup()
	{
		$instance = new JDatabaseImporterPostgresqlInspector;

		$o1 = (object) array('Index' => 'id', 'foo' => 'bar1');
		$o2 = (object) array('Index' => 'id', 'foo' => 'bar2');
		$o3 = (object) array('Index' => 'title', 'foo' => 'bar3');

		$this->assertThat(
			$instance->getIdxLookup(
				array($o1, $o2, $o3)
			),
			$this->equalTo(
				array(
					'id' => array($o1, $o2),
					'title' => array($o3)
				)
			),
			'getIdxLookup, using array input, did not yield the expected result.'
		);

		$o1 = new SimpleXmlElement('<key Index="id" foo="bar1" />');
		$o2 = new SimpleXmlElement('<key Index="id" foo="bar2" />');
		$o3 = new SimpleXmlElement('<key Index="title" foo="bar3" />');

		$this->assertThat(
			$instance->getIdxLookup(
				array($o1, $o2, $o3)
			),
			$this->equalTo(
				array(
					'id' => array($o1, $o2),
					'title' => array($o3)
				)
			),
			'getIdxLookup, using SimpleXmlElement input, did not yield the expected result.'
		);
	}

	/**
	 * Tests the getRealTableName method with the wrong type of class.
	 *
	 * @return void
	 *
	 * @since  12.1
	 */
	public function testGetRealTableName()
	{
		$instance	= new JDatabaseImporterPostgresqlInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getRealTableName('#__test'),
			$this->equalTo('jos_test'),
			'getRealTableName should return the name of the table with #__ converted to the database prefix.'
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
		$instance	= new JDatabaseImporterPostgresqlInspector;

		try
		{
			$instance->setDbo(new stdClass);
		}
		catch (PHPUnit_Framework_Error $e)
		{
			// Expecting the error, so just ignore it.
			return;
		}

		$this->fail(
			'setDbo requires a JDatabaseDriverPostgresql object and should throw an exception.'
		);
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
		$instance = new JDatabaseImporterPostgresqlInspector;

		try
		{
			$result = $instance->setDbo($this->dbo);

			$this->assertThat(
				$result,
				$this->identicalTo($instance),
				'setDbo must return an object to support chaining.'
			);

		}
		catch (PHPUnit_Framework_Error $e)
		{
			// Unknown error has occurred.
			$this->fail(
				$e->getMessage()
			);
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
		$instance = new JDatabaseImporterPostgresqlInspector;

		$result = $instance->withStructure();

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'withStructure must return an object to support chaining.'
		);

		$this->assertThat(
			$instance->options->withStructure,
			$this->isTrue(),
			'The default use of withStructure should result in true.'
		);

		$instance->withStructure(true);
		$this->assertThat(
			$instance->options->withStructure,
			$this->isTrue(),
			'The explicit use of withStructure with true should result in true.'
		);

		$instance->withStructure(false);
		$this->assertThat(
			$instance->options->withStructure,
			$this->isFalse(),
			'The explicit use of withStructure with false should result in false.'
		);
	}
}
