<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Tests the JDatabaseExporterMysql class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 */
class JDatabaseExporterMysqlTest extends TestCase
{
	private $xmlDump = '<?xml version="1.0"?>
<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <database name="">
  <table_structure name="#__test">
   <field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />
   <field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />
   <key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />
  </table_structure>
 </database>
</mysqldump>';

	/**
	 * @var    JDatabaseDriverMysql  The mocked database object for use by test methods.
	 */
	protected $dbo = null;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		if (PHP_MAJOR_VERSION >= 7)
		{
			self::markTestSkipped('ext/mysql is unsupported on PHP 7.');
		}
	}

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->dbo = $this->getMockDatabase('Mysql');

		$this->dbo
			->expects($this->any())
			->method('getPrefix')
			->willReturn('jos_');

		$this->dbo
			->expects($this->any())
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

		$this->dbo
			->expects($this->any())
			->method('getTableKeys')
			->willReturn(
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
			);

		$this->dbo
			->expects($this->any())
			->method('loadObjectList')
			->willReturnCallback(array($this, 'callbackLoadObjectList'));

		parent::setUp();
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
		parent::tearDown();
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
		$instance = new JDatabaseExporterMysql;

		$instance
			->setDbo($this->dbo)
			->from('#__test')
			->withStructure(true);

		$expecting = $this->xmlDump;

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
		$instance = new JDatabaseExporterMysql;

		$result = $instance->asXml();

		$this->assertSame(
			$instance,
			$result
		);

		$this->assertSame(
			'xml',
			TestReflection::getValue($instance, 'asFormat')
		);
	}

	/**
	 * Test the buildXML method.
	 */
	public function testBuildXml()
	{
		$instance = new JDatabaseExporterMysql;

		// Set up the export settings.
		$instance
			->setDbo($this->dbo)
			->from('jos_test')
			->withStructure(true);

		$expecting = $this->xmlDump;

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
		$instance = new JDatabaseExporterMysql;

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
		$instance = new JDatabaseExporterMysql;

		$instance->check();
	}

	/**
	 * Tests the check method.
	 *
	 * @expectedException Exception
	 */
	public function testCheckWithNoTables()
	{
		$instance = new JDatabaseExporterMysql;

		$instance->setDbo($this->dbo);

		$instance->check();
	}

	/**
	 * Tests the check method.
	 */
	public function testCheckWithGoodInput()
	{
		$instance = new JDatabaseExporterMysql;

		$instance->setDbo($this->dbo);
		$instance->from('foobar');

		$this->assertSame(
			$instance,
			$instance->check()
		);
	}

	/**
	 * Tests the from method with bad input.
	 *
	 * @expectedException Exception
	 */
	public function testFromWithBadInput()
	{
		$instance = new JDatabaseExporterMysql;

		$instance->from(new stdClass);
	}

	/**
	 * Tests the from method with expected good inputs.
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseExporterMysql;

		$this->assertSame(
			$instance,
			$instance->from('jos_foobar')
		);

		$this->assertSame(
			array('jos_foobar'),
			TestReflection::getValue($instance, 'from')
		);
	}

	/**
	 * Tests the method getGenericTableName method.
	 */
	public function testGetGenericTableName()
	{
		$instance = new JDatabaseExporterMysql;

		$instance->setDbo($this->dbo);

		$this->assertSame(
			'#__test',
			TestReflection::invoke($instance, 'getGenericTableName', 'jos_test')
		);
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 */
	public function testSetDboWithGoodInput()
	{
		$instance = new JDatabaseExporterMysql;

		$this->assertSame(
			$instance,
			$instance->setDbo($this->dbo)
		);
	}

	/**
	 * Tests the withStructure method.
	 */
	public function testWithStructure()
	{
		$instance = new JDatabaseExporterMysql;

		$this->assertSame(
			$instance,
			$instance->withStructure()
		);

		$this->assertTrue(
			TestReflection::getValue($instance, 'options')->withStructure
		);

		$instance->withStructure(true);

		$this->assertTrue(
			TestReflection::getValue($instance, 'options')->withStructure
		);

		$instance->withStructure(false);

		$this->assertFalse(
			TestReflection::getValue($instance, 'options')->withStructure
		);
	}
}
