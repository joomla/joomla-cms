<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Tests the JDatabaseExporterPdomysql class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       3.4
 */
class JDatabaseExporterPdomysqlTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    object  The mocked database object for use by test methods.
	 * @since  3.4
	 */
	protected $dbo = null;

	/**
	 * Sets up the testing conditions
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function setup()
	{
		parent::setUp();

		// Set up the database object mock.
		$this->dbo = $this->getMock(
			'JDatabaseDriverPdomysql',
			array(
				'getErrorNum',
				'getPrefix',
				'getTableColumns',
				'getTableKeys',
				'quoteName',
				'loadObjectList',
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
	 * @return  array  An array of results based on the setting of the last query.
	 *
	 * @since   3.4
	 */
	public function callbackLoadObjectList()
	{
		return array();
	}

	/**
	 * Callback for the dbo quoteName method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in MySQL quotes.
	 *
	 * @since   3.4
	 */
	public function callbackQuoteName($value)
	{
		return "`$value`";
	}

	/**
	 * Callback for the dbo setQuery method.
	 *
	 * @param   string  $query  The query.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function callbackSetQuery($query)
	{
		$this->lastQuery = $query;
	}

	/**
	 * Test the magic __toString method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function test__toString()
	{
		$instance = new JDatabaseExporterPdomysql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('#__test')
			->withStructure(true);

		$expecting = '<?xml version="1.0"?>
<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />
   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />
   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />
  </table_structure>
 </database>
</mysqldump>';

		$this->assertThat(
			preg_replace('/\v/', '', (string) $instance),
			$this->equalTo(
				preg_replace('/\v/', '', $expecting)
			),
			'__toString has not returned the expected result.'
		);
	}

	/**
	 * Tests the asXml method.
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testAsXml()
	{
		$instance = new JDatabaseExporterPdomysql;

		$result = $instance->asXml();

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'asXml must return an object to support chaining.'
		);

		$this->assertThat(
			TestReflection::getValue($instance, 'asFormat'),
			$this->equalTo('xml'),
			'The asXml method should set the protected asFormat property to "xml".'
		);
	}

	/**
	 * Test the buildXML method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testBuildXml()
	{
		$instance = new JDatabaseExporterPdomysql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('#__test')
			->withStructure(true);

		$expecting = '<?xml version="1.0"?>
<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />
   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />
   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />
  </table_structure>
 </database>
</mysqldump>';

		// Replace used to prevent platform conflicts
		$this->assertThat(
			preg_replace('/\v/', '', TestReflection::invoke($instance, 'buildXml')),
			$this->equalTo(
				preg_replace('/\v/', '', $expecting)
			),
			'buildXml has not returned the expected result.'
		);
	}

	/**
	 * Tests the buildXmlStructure method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testBuildXmlStructure()
	{
		$instance = new JDatabaseExporterPdomysql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('#__test')
			->withStructure(true);

		$this->assertThat(
			TestReflection::invoke($instance, 'buildXmlStructure'),
			$this->equalTo(
				array(
					'  <table_structure name="#__test">',
					'   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />',
					'   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />',
					'   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" ' .
					'Null="" Index_type="BTREE" Comment="" />',
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
	 *
	 * @since  3.4
	 */
	public function testCheckWithNoDbo()
	{
		$instance = new JDatabaseExporterPdomysql;

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
	 * @since  3.4
	 */
	public function testCheckWithNoTables()
	{
		$instance = new JDatabaseExporterPdomysql;
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
	 * @since  3.4
	 */
	public function testCheckWithGoodInput()
	{
		$instance = new JDatabaseExporterPdomysql;
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
	 * Tests the from method with bad input.
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testFromWithBadInput()
	{
		$instance = new JDatabaseExporterPdomysql;

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
	 *
	 * @since  3.4
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseExporterPdomysql;

		try
		{
			$result = $instance->from('jos_foobar');

			$this->assertThat(
				$result,
				$this->identicalTo($instance),
				'from must return an object to support chaining.'
			);

			$this->assertThat(
				TestReflection::getValue($instance, 'from'),
				$this->equalTo(array('jos_foobar')),
				'The from method should convert a string input to an array.'
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
	 * Tests the method getGenericTableName method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetGenericTableName()
	{
		$instance = new JDatabaseExporterPdomysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getGenericTableName', 'jos_test'),
			$this->equalTo('#__test'),
			'The testGetGenericTableName should replace the database prefix with #__.'
		);
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testSetDboWithGoodInput()
	{
		$instance = new JDatabaseExporterPdomysql;

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
	 * @since   3.4
	 */
	public function testWithStructure()
	{
		$instance = new JDatabaseExporterPdomysql;

		$result   = $instance->withStructure();

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'withStructure must return an object to support chaining.'
		);

		$options = TestReflection::getValue($instance, 'options');

		$this->assertThat(
			$options->withStructure,
			$this->isTrue(),
			'The default use of withStructure should result in true.'
		);

		$instance->withStructure(true);

		$options = TestReflection::getValue($instance, 'options');

		$this->assertThat(
			$options->withStructure,
			$this->isTrue(),
			'The explicit use of withStructure with true should result in true.'
		);

		$instance->withStructure(false);

		$options = TestReflection::getValue($instance, 'options');

		$this->assertThat(
			$options->withStructure,
			$this->isFalse(),
			'The explicit use of withStructure with false should result in false.'
		);
	}
}
