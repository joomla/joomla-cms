<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

require_once dirname(__FILE__).'/JDatabaseMySqlExporterInspector.php';

/**
 * Tests the JDatabaseMySqlExporter class.
 *
 * @package    Joomla.UnitTest
 * @subpackage Database
 * @since      11.1
 */
class JDatabaseMySqlExporterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    object  The mocked database object for use by test methods.
	 * @since  11.1
	 */
	protected $dbo = null;

	/**
	 * @var    string  The last query sent to the dbo setQuery method.
	 * @since  11.1
	 */
	protected $lastQuery = '';

	/**
	 * Sets up the testing conditions
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function setup()
	{
		// Set up the database object mock.

		$this->dbo = $this->getMock(
			'JDatabaseMySql',
			array(
				'getErrorNum',
				'getPrefix',
				'quoteName',
				'loadObjectList',
				'setQuery'
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
		->method('quoteName')
		->will(
			$this->returnCallback(
				array($this, 'callbackQuoteName')
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
	 * @since  11.1
	 */
	public function callbackLoadObjectList()
	{
		if ($this->lastQuery == 'SHOW FULL COLUMNS FROM `#__test`') {
			return array(
				(object) array(
					'Field' => 'id',
					'Type' => 'int(11) unsigned',
					'Collation' => null,
					'Null' => 'NO',
					'Key' => 'PRI',
					'Default' => '',
					'Extra' => 'auto_increment',
					'Privileges' => 'select,insert,update,references',
					'Comment' => '',
				),
				(object) array(
					'Field' => 'title',
					'Type' => 'varchar(255)',
					'Collation' => 'utf8_general_ci',
					'Null' => 'NO',
					'Key' => '',
					'Default' => '',
					'Extra' => '',
					'Privileges' => 'select,insert,update,references',
					'Comment' => '',
				),
			);
		}
		else if ($this->lastQuery == 'SHOW KEYS FROM `#__test`') {
			return array(
				(object) array(
					'Table' => 'jos_test',
		            'Non_unique' => '0',
		            'Key_name' => 'PRIMARY',
		            'Seq_in_index' => '1',
		            'Column_name' => 'id',
		            'Collation' => 'A',
		            'Cardinality' => '2695',
		            'Sub_part' => '',
		            'Packed' => '',
		            'Null' => '',
		            'Index_type' => 'BTREE',
		            'Comment' => '',
				),
			);
		}
	}

	/**
	 * Callback for the dbo quoteName method.
	 *
	 * @param  string  $value  The value to be quoted.
	 *
	 * @return string  The value passed wrapped in MySQL quotes.
	 * @since  11.1
	 */
	public function callbackQuoteName($value)
	{
		return "`$value`";
	}

	/**
	 * Callback for the dbo setQuery method.
	 *
	 * @param  string  $query  The query.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function callbackSetQuery($query)
	{
		$this->lastQuery = $query;
	}

	/**
	 * Test the magic __toString method.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function test__toString()
	{
		$instance = new JDatabaseMySqlExporterInspector;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true)
			;

		$this->assertThat(
			(string) $instance,
			$this->equalTo(
'<?xml version="1.0"?>
<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />
   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />
   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />
  </table_structure>
 </database>
</mysqldump>'
			),
			'__toString has not returned the expected result.'
		);

	}

	/**
	 * Tests the asXml method.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testAsXml()
	{
		$instance = new JDatabaseMySqlExporterInspector;

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
	 * Test the buildXML method.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function testBuildXml()
	{
		$instance = new JDatabaseMySqlExporterInspector;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true)
			;

		$this->assertThat(
			$instance->buildXml(),
			$this->equalTo(
'<?xml version="1.0"?>
<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />
   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />
   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />
  </table_structure>
 </database>
</mysqldump>'
			),
			'buildXml has not returned the expected result.'
		);
	}

	/**
	 * Tests the buildXmlStructure method.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function testBuildXmlStructure()
	{
		$instance = new JDatabaseMySqlExporterInspector;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true)
			;

		$this->assertThat(
			$instance->buildXmlStructure(),
			$this->equalTo(
				array(
					'  <table_structure name="#__test">',
					'   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />',
					'   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />',
					'   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />',
					'  </table_structure>'
				)
			),
			'buildXmlStructure has not returned the expected result.'
		);
	}

	/**
	 * Tests the check method.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testCheckWithNoDbo()
	{
		$instance = new JDatabaseMySqlExporterInspector;

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
	 * @since  11.1
	 */
	public function testCheckWithNoTables()
	{
		$instance	= new JDatabaseMySqlExporterInspector;
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
	 * @since  11.1
	 */
	public function testCheckWithGoodInput()
	{
		$instance	= new JDatabaseMySqlExporterInspector;
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
				'Check method should not throw exception with good setup: '.$e->getMessage()
			);
		}
	}

	/**
	 * Tests the from method with bad input.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testFromWithBadInput()
	{
		$instance = new JDatabaseMySqlExporterInspector;

		try
		{
			$instance->from(new stdClass);
		}
		catch (Exception $e)
		{
			// Exception expected.
			return;
		}

		$this->fail(
			'From method should thrown an exception if argument is not a string or array.'
		);
	}

	/**
	 * Tests the from method with expected good inputs.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseMySqlExporterInspector;

		try
		{
			$result = $instance->from('jos_foobar');

			$this->assertThat(
				$result,
				$this->identicalTo($instance),
				'from must return an object to support chaining.'
			);

			$this->assertThat(
				$instance->from,
				$this->equalTo(array('jos_foobar')),
				'The from method should convert a string input to an array.'
			);
		}
		catch (Exception $e)
		{
			$this->fail(
				'From method should not throw exception with good input: '.$e->getMessage()
			);
		}
	}

	/**
	 * Tests the getColumns method.
	 *
	 * Note this method tests mock data.
	 * It will not detect a change in the expected data format from the database.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testGetColumns()
	{
		$instance = new JDatabaseMySqlExporterInspector;
		$instance->setDbo($this->dbo);

		try
		{
			$result = $instance->getColumns('#__test');

			$this->assertThat(
				is_array($result),
				$this->isTrue(),
				'getColumns method should return an array matching the sample data.'
			);
		}
		catch (PHPUnit_Framework_Error $e)
		{
			// Enexpect error has occurred.
			$this->fail(
				$e->getMessage()
			);
		}
	}

	/**
	 * Tests the method getGenericTableName method.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public function testGetGenericTableName()
	{
		$instance = new JDatabaseMySqlExporterInspector;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			$instance->getGenericTableName('jos_test'),
			$this->equalTo('#__test'),
			'The testGetGenericTableName should replace the database prefix with #__.'
		);
	}

	/**
	 * Tests the getKeys method.
	 *
	 * Note this method tests mock data.
	 * It will not detect a change in the expected data format from the database.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testGetKeys()
	{
		$instance = new JDatabaseMySqlExporterInspector;
		$instance->setDbo($this->dbo);

		try
		{
			$result = $instance->getKeys('#__test');

			$this->assertThat(
				is_array($result),
				$this->isTrue(),
				'getKeys method should return an array matching the sample data.'
			);
		}
		catch (PHPUnit_Framework_Error $e)
		{
			// Enexpect error has occurred.
			$this->fail(
				$e->getMessage()
			);
		}
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testSetDboWithBadInput()
	{
		$instance	= new JDatabaseMySqlExporterInspector;

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
			'setDbo requires a JDatabaseMySql object and should throw an exception.'
		);
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 *
	 * @return void
	 * @since  11.1
	 */
	public function testSetDboWithGoodInput()
	{
		$instance = new JDatabaseMySqlExporterInspector;

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
	 * @since   11.1
	 */
	public function testWithStructure()
	{
		$instance = new JDatabaseMySqlExporterInspector;

		$result = $instance->withStructure();

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'withStructure must return an object to support chaining.'
		);

		$this->assertThat(
			$instance->options->get('with-structure'),
			$this->isTrue(),
			'The default use of withStructure should result in true.'
		);

		$instance->withStructure(true);
		$this->assertThat(
			$instance->options->get('with-structure'),
			$this->isTrue(),
			'The explicit use of withStructure with true should result in true.'
		);

		$instance->withStructure(false);
		$this->assertThat(
			$instance->options->get('with-structure'),
			$this->isFalse(),
			'The explicit use of withStructure with false should result in false.'
		);
	}
}
