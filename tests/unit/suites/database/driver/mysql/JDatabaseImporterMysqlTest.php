<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Tests the JDatabaseImporterMysql class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 */
class JDatabaseImporterMysqlTest extends TestCase
{
	/**
	 * @var    object  The mocked database object for use by test methods.
	 */
	protected $dbo = null;

	/**
	 * @var    string  The last query sent to the dbo setQuery method.
	 */
	protected $lastQuery = '';

	/**
	 * @var    array  Selected sample data for tests.
	 */
	protected $sample = array(
		'xml-id-field' =>
			'<field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />',
		'xml-title-field' =>
			'<field Field="title" Type="varchar(50)" Null="NO" Key="" Default="" Extra="" />',
		'xml-body-field' =>
			'<field Field="body" Type="mediumtext" Null="NO" Key="" Default="" Extra="" />',
		'xml-primary-key' =>
			'<key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1" Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />',
	);

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
		$this->dbo = $this->getMock(
			'JDatabaseDriverMysql',
			array(
				'getErrorNum',
				'getPrefix',
				'getTableColumns',
				'getTableKeys',
				'quoteName',
				'loadObjectList',
				'quote',
				'setQuery',
			),
			array(),
			'',
			false
		);

		$this->dbo
			->expects($this->any())
			->method('getPrefix')
			->will(
				$this->returnValue('jos_')
		);

		$this->dbo
			->expects($this->any())
			->method('getTableColumns')
			->will(
			$this->returnValue(
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
			)
		);

		$this->dbo
			->expects($this->any())
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

		$this->dbo
			->expects($this->any())
			->method('quoteName')
			->will(
			$this->returnCallback(
				array($this, 'callbackQuoteName')
			)
		);

		$this->dbo
			->expects($this->any())
			->method('quote')
			->will(
			$this->returnCallback(
				array($this, 'callbackQuote')
			)
		);

		$this->dbo
			->expects($this->any())
			->method('setQuery')
			->will(
			$this->returnCallback(
				array($this, 'callbackSetQuery')
			)
		);

		$this->dbo
			->expects($this->any())
			->method('loadObjectList')
			->will(
			$this->returnCallback(
				array($this, 'callbackLoadObjectList')
			)
		);
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
	 * @return array  An array of results based on the setting of the last query.
	 */
	public function callbackLoadObjectList()
	{
		return array();
	}

	/**
	 * Callback for the dbo quote method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return string  The value passed wrapped in MySQL quotes.
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
	 * @return string  The value passed wrapped in MySQL quotes.
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
	 * @return void
	 */
	public function callbackSetQuery($query)
	{
		$this->lastQuery = $query;
	}

	/**
	 * Data for the testGetAlterTableSQL test.
	 *
	 * @return  array  Each array element must be an array with 3 elements: SimpleXMLElement field, expected result, error message.
	 */
	public function dataGetAlterTableSql()
	{
		$f1 = '<field Field="id" Type="int(11) unsigned" Null="NO" Key="PRI" Default="" Extra="auto_increment" />';
		$f2 = '<field Field="title" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />';
		$f3 = '<field Field="alias" Type="varchar(255)" Null="NO" Key="" Default="" Extra="" />';

		$k1 = '<key Table="#__test" Non_unique="0" Key_name="PRIMARY" Seq_in_index="1"' .
			' Column_name="id" Collation="A" Null="" Index_type="BTREE" Comment="" />';
		$k2 = '<key Table="#__test" Non_unique="0" Key_name="idx_title" Seq_in_index="1"' .
			' Column_name="title" Collation="A" Null="" Index_type="BTREE" Comment="" />';

		return array(
			array(
				new SimpleXmlElement('<table_structure name="#__test">' . $f1 . $f2 . $k1 . '</table_structure>'),
				array(),
				'getAlterTableSQL should not change anything.'
			),
			array(
				new SimpleXmlElement('<table_structure name="#__test">' . $f1 . $f2 . $f3 . $k1 . '</table_structure>'),
				array(
					"ALTER TABLE `jos_test` ADD COLUMN `alias` varchar(255) NOT NULL DEFAULT ''",
				),
				'getAlterTableSQL should add the new alias column.'
			),
			array(
				new SimpleXmlElement('<table_structure name="#__test">' . $f1 . $f2 . $k1 . $k2 . '</table_structure>'),
				array(
					"ALTER TABLE `jos_test` ADD UNIQUE KEY `idx_title` (`title`)",
				),
				'getAlterTableSQL should add the new key.'
			),
			array(
				new SimpleXmlElement('<table_structure name="#__test">' . $f1 . $k1 . '</table_structure>'),
				array(
					"ALTER TABLE `jos_test` DROP COLUMN `title`",
				),
				'getAlterTableSQL should remove the title column.'
			),
			array(
				new SimpleXmlElement('<table_structure name="#__test">' . $f1 . $f2 . '</table_structure>'),
				array(
					"ALTER TABLE `jos_test` DROP PRIMARY KEY",
				),
				'getAlterTableSQL should drop the old primary key.'
			),
		);
	}

	/**
	 * Data for the testGetColumnSQL test.
	 *
	 * @return  array  Each array element must be an array with 3 elements: SimpleXMLElement field, expected result, error message.
	 */
	public function dataGetColumnSql()
	{
		return array(
			array(
				new SimpleXmlElement(
					$this->sample['xml-id-field']
				),
				"`id` int(11) unsigned NOT NULL DEFAULT '' AUTO_INCREMENT",
				'Typical primary key field',
			),
			array(
				new SimpleXmlElement(
					$this->sample['xml-title-field']
				),
				"`title` varchar(50) NOT NULL DEFAULT ''",
				'Typical text field',
			),
			array(
				new SimpleXmlElement(
					$this->sample['xml-body-field']
				),
				"`body` mediumtext NOT NULL",
				'Typical blob field',
			),
		);
	}

	/**
	 * Data for the testGetColumnSQL test.
	 *
	 * @return  array  Each array element must be an array with 3 elements: SimpleXMLElement field, expected result, error message.
	 */
	public function dataGetKeySql()
	{
		return array(
			array(
				// Keys come in arrays.
				array(
					new SimpleXmlElement(
						$this->sample['xml-primary-key']
					),
				),
				"primary key  (`id`)",
				'Typical primary key index',
			),
		);
	}

	/**
	 * Tests the asXml method.
	 *
	 * @return void
	 */
	public function testAsXml()
	{
		$instance = new JDatabaseImporterMysql;

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
	 * Tests the check method.
	 *
	 * @expectedException Exception
	 */
	public function testCheckWithNoDbo()
	{
		$instance = new JDatabaseImporterMysql;

		$instance->check();
	}

	/**
	 * Tests the check method.
	 *
	 * @expectedException Exception
	 */
	public function testCheckWithNoFrom()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$instance->check();
	}

	/**
	 * Tests the check method.
	 *
	 * @return void
	 */
	public function testCheckWithGoodInput()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);
		$instance->from('foobar');

		$result = $instance->check();

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'check must return an object to support chaining.'
		);
	}

	/**
	 * Tests the from method with expected good inputs.
	 */
	public function testFromWithGoodInput()
	{
		$instance = new JDatabaseImporterMysql;

		$result = $instance->from('foobar');

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'from must return an object to support chaining.'
		);

		$this->assertThat(
			TestReflection::getValue($instance, 'from'),
			$this->equalTo('foobar'),
			'The from method did not store the value as expected.'
		);
	}

	/**
	 * Tests the getAddColumnSQL method.
	 *
	 * Note that combinations of fields is tested in testGetColumnSQL.
	 */
	public function testGetAddColumnSql()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getAddColumnSql', 'jos_test', new SimpleXmlElement($this->sample['xml-title-field'])),
			$this->equalTo(
				"ALTER TABLE `jos_test` ADD COLUMN `title` varchar(50) NOT NULL DEFAULT ''"
			),
			'testGetAddColumnSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getAddKeySQL method.
	 *
	 * Note that combinations of keys is tested in testGetKeySQL.
	 */
	public function testGetAddKeySql()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getAddKeySQL', 'jos_test', array(new SimpleXmlElement($this->sample['xml-primary-key']))),
			$this->equalTo(
				"ALTER TABLE `jos_test` ADD PRIMARY KEY  (`id`)"
			),
			'testGetAddKeySQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getAlterTableSQL method.
	 *
	 * @param   string $structure
	 * @param   string $expected
	 * @param   string $message
	 *
	 * @dataProvider dataGetAlterTableSQL
	 */
	public function testGetAlterTableSql($structure, $expected, $message)
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getAlterTableSQL', $structure),
			$this->equalTo(
				$expected
			),
			$message
		);
	}

	/**
	 * Tests the getChangeColumnSQL method.
	 *
	 * Note that combinations of fields is tested in testGetColumnSQL.
	 */
	public function testGetChangeColumnSql()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getChangeColumnSQL', 'jos_test', new SimpleXmlElement($this->sample['xml-title-field'])),
			$this->equalTo(
				"ALTER TABLE `jos_test` CHANGE COLUMN `title` `title` varchar(50) NOT NULL DEFAULT ''"
			),
			'getChangeColumnSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getColumnSQL method.
	 *
	 * @param   string $field
	 * @param   string  $expected  The expected result from the getColumnSQL method.
	 * @param   string  $message   The error message to display if the result does not match the expected value.
	 *
	 * @dataProvider dataGetColumnSQL
	 */
	public function testGetColumnSql($field, $expected, $message)
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			strtolower(TestReflection::invoke($instance, 'getColumnSQL', $field)),
			$this->equalTo(strtolower($expected)),
			$message
		);
	}

	/**
	 * Tests the getDropColumnSQL method.
	 */
	public function testGetDropColumnSql()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getDropColumnSQL', 'jos_test', 'title'),
			$this->equalTo(
				"ALTER TABLE `jos_test` DROP COLUMN `title`"
			),
			'getDropColumnSQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getDropKeySQL method.
	 */
	public function testGetDropKeySql()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getDropKeySQL', 'jos_test', 'idx_title'),
			$this->equalTo(
				"ALTER TABLE `jos_test` DROP KEY `idx_title`"
			),
			'getDropKeySQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getDropPrimaryKeySQL method.
	 */
	public function testGetDropPrimaryKeySql()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getDropPrimaryKeySQL', 'jos_test'),
			$this->equalTo(
				"ALTER TABLE `jos_test` DROP PRIMARY KEY"
			),
			'getDropPrimaryKeySQL did not yield the expected result.'
		);
	}

	/**
	 * Tests the getKeyLookup method.
	 */
	public function testGetKeyLookup()
	{
		$instance = new JDatabaseImporterMysql;

		$o1 = (object) array('Key_name' => 'id', 'foo' => 'bar1');
		$o2 = (object) array('Key_name' => 'id', 'foo' => 'bar2');
		$o3 = (object) array('Key_name' => 'title', 'foo' => 'bar3');

		$this->assertThat(
			TestReflection::invoke($instance, 'getKeyLookup', array($o1, $o2, $o3)),
			$this->equalTo(
				array(
					'id' => array($o1, $o2),
					'title' => array($o3)
				)
			),
			'getKeyLookup, using array input, did not yield the expected result.'
		);

		$o1 = new SimpleXmlElement('<key Key_name="id" foo="bar1" />');
		$o2 = new SimpleXmlElement('<key Key_name="id" foo="bar2" />');
		$o3 = new SimpleXmlElement('<key Key_name="title" foo="bar3" />');

		$this->assertThat(
			TestReflection::invoke($instance, 'getKeyLookup', array($o1, $o2, $o3)),
			$this->equalTo(
				array(
					'id' => array($o1, $o2),
					'title' => array($o3)
				)
			),
			'getKeyLookup, using SimpleXmlElement input, did not yield the expected result.'
		);
	}

	/**
	 * Tests the getKeySQL method.
	 *
	 * @param   string $field
	 * @param   string  $expected  The expected result from the getKeySQL method.
	 * @param   string  $message   The error message to display if the result does not match the expected value.
	 *
	 * @dataProvider dataGetKeySQL
	 */
	public function testGetKeySql($field, $expected, $message)
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			strtolower(TestReflection::invoke($instance, 'getKeySQL', $field)),
			$this->equalTo(strtolower($expected)),
			$message
		);
	}

	/**
	 * Tests the getRealTableName method with the wrong type of class.
	 */
	public function testGetRealTableName()
	{
		$instance = new JDatabaseImporterMysql;
		$instance->setDbo($this->dbo);

		$this->assertThat(
			TestReflection::invoke($instance, 'getRealTableName', '#__test'),
			$this->equalTo('jos_test'),
			'getRealTableName should return the name of the table with #__ converted to the database prefix.'
		);
	}

	/**
	 * Tests the setDbo method with the wrong type of class.
	 */
	public function testSetDboWithGoodInput()
	{
		$instance = new JDatabaseImporterMysql;

		$result = $instance->setDbo($this->dbo);

		$this->assertThat(
			$result,
			$this->identicalTo($instance),
			'setDbo must return an object to support chaining.'
		);
	}

	/**
	 * Tests the withStructure method.
	 */
	public function testWithStructure()
	{
		$instance = new JDatabaseImporterMysql;

		$result = $instance->withStructure();

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
