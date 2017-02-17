<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Tests the JDatabaseExporterMysqli class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 */
class JDatabaseExporterMysqliTest extends TestCase
{
	/**
	 * @var    JDatabaseDriverMysqli  The mocked database object for use by test methods.
	 */
	protected $dbo;

	/**
	 * Sets up the testing conditions
	 *
	 * @return  void
	 */
	public function setup()
	{
		$this->dbo = $this->getMockDatabase('Mysqli');

		$this->dbo->expects($this->any())
			->method('getPrefix')
			->willReturn('jos_');

		$this->dbo->expects($this->any())
			->method('getTableColumns')
			->willReturn(
				array(
					'id' => (object) array(
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
					'title' => (object) array(
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
			);

		$this->dbo->expects($this->any())
			->method('getTableKeys')
			->willReturn(array(
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
			));

		$this->dbo->expects($this->any())
			->method('loadObjectList')
			->willReturnCallback(array($this, 'callbackLoadObjectList'));
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
	 * Callback for the dbo loadObjectList method.
	 *
	 * @return  array  An array of results based on the setting of the last query.
	 */
	public function callbackLoadObjectList()
	{
		return array();
	}

	/**
	 * Test the magic __toString method.
	 */
	public function test__toString()
	{
		$instance = new JDatabaseExporterMysqli;

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
		$instance = new JDatabaseExporterMysqli;

		$result = $instance->asXml();

		$this->assertSame(
			$instance,
			$result,
			'asXml must return an object to support chaining.'
		);

		$this->assertSame(
			'xml',
			TestReflection::getValue($instance, 'asFormat'),
			'The asXml method should set the protected asFormat property to "xml".'
		);
	}

	/**
	 * Test the buildXML method.
	 */
	public function testBuildXml()
	{
		$instance = new JDatabaseExporterMysqli;

		$instance
			->setDbo($this->dbo)
			->from('jos_test')
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
		$instance = new JDatabaseExporterMysqli;

		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true);

		$this->assertEquals(
			array(
				'  <table_structure name="#__test">',
				'   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />',
				'   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />',
				'   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" ' .
				'Null="" Index_type="BTREE" Comment="" />',
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
		$instance = new JDatabaseExporterMysqli;

		$instance->check();
	}

	/**
	 * Tests the check method.
	 *
	 * @expectedException Exception
	 */
	public function testCheckWithNoTables()
	{
		$instance = new JDatabaseExporterMysqli;
		$instance->setDbo($this->dbo);

		$instance->check();
	}

	/**
	 * Tests the check method.
	 */
	public function testCheckWithGoodInput()
	{
		$instance = new JDatabaseExporterMysqli;
		$instance->setDbo($this->dbo);
		$instance->from('foobar');

		$result = $instance->check();

		$this->assertSame(
			$instance,
			$result,
			'check must return an object to support chaining.'
		);
	}

	/**
	 * Tests the from method with bad input.
	 *
	 * @expectedException Exception
	 */
	public function testFromWithBadInput()
	{
		$instance = new JDatabaseExporterMysqli;

		$instance->from(new stdClass);
	}

	/**
	 * Tests the from method with expected good inputs.
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseExporterMysqli;

		$result = $instance->from('jos_foobar');

		$this->assertSame(
			$instance,
			$result,
			'from must return an object to support chaining.'
		);

		$this->assertSame(
			array('jos_foobar'),
			TestReflection::getValue($instance, 'from'),
			'The from method should convert a string input to an array.'
		);
	}

	/**
	 * Tests the method getGenericTableName method.
	 */
	public function testGetGenericTableName()
	{
		$instance = new JDatabaseExporterMysqli;
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
		$instance = new JDatabaseExporterMysqli;

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
		$instance = new JDatabaseExporterMysqli;

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
