<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Serverclient.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1ServerclientTest extends TestCase
{
	/**
	 * The test object.
	 * @var JOAuth1Serverclient
	 */
	protected $object;

	/**
	 * The DB object.
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * The options object.
	 * @var JDatabaseDriver
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->getMockBuilder('JDatabaseDriverMysqli')
			->disableOriginalConstructor()
			->disableArgumentCloning()
			->getMock();

		$this->object = new JOAuth1Serverclient($this->db);
	}

	/**
	 * Tests the __get method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__get()
	{
		TestReflection::setValue($this->object, '_properties', array('alias' => 'value'));

		$this->assertEquals('value', $this->object->alias);
	}

	/**
	 * Tests the __set method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__set()
	{
		$this->object->alias = 'value';

		$this->assertEquals(
			array(
				'alias' => 'value',
				'client_id' => '',
				'key' => '',
				'secret' => '',
				'title' => '',
				'callback' => '',
				'resource_owner_id' => ''
			),
			TestReflection::getValue($this->object, '_properties')
		);
	}

	/**
	 * Tests the modifyObject method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function modifyObject($object, $property, $value, $returnValue)
	{
		$object->$property = $value;

		return $this->returnValue($returnValue);
	}

	/**
	 * Tests the create method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreate()
	{
		$clientArray = array(
			'client_id' => '',
			'alias' => 'Alias1',
			'key' => 'Key1',
			'secret' => 'Secret1',
			'title' => 'Title1',
			'callback' => 'oob',
			'resource_owner_id' => ''
		);

		TestReflection::setValue(
			$this->object,
			'_properties',
			$clientArray
		);

		$clientObject = (object) $clientArray;

		unset($clientObject->client_id);

		$this->db->expects($this->once())
			->method('insertObject')
			->will($this->returnCallback(array($this, 'insertObjectCallback')));

		$this->object->create();

		$this->assertEquals('5', $this->object->client_id);
	}

	/**
	 * Callback to modify clientObject to set the primary key.
	 *
	 * @param   string    $table         The name of the table.
	 * @param   stdClass  $clientObject  The client object.
	 * @param   string    $key           The name of the key for the table.
	 *
	 * @return  boolean   Returns true.
	 */
	public function insertObjectCallback($table, $clientObject, $key)
	{
		$this->assertEquals('#__oauth_clients', $table);
		$this->assertEquals('client_id', $key);
		$clientObject->client_id = 5;

		return true;
	}

	/**
	 * Tests the create method (not a new client).
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreateExists()
	{
		$this->object->client_id = 5;

		$this->assertFalse($this->object->create());
	}

	/**
	 * Tests the delete method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDelete()
	{
		$this->object->client_id = 5;

		$query = $this->getMockBuilder('JDatabaseQueryMysqli')
			->disableOriginalConstructor()
			->getMock();

		$this->db->expects($this->once())
			->method('getQuery')
			->with(true)
			->will($this->returnValue($query));

		$query->expects($this->once())
			->method('delete')
			->with('#__oauth_clients')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('client_id = 5')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('execute');

		$this->object->delete();
	}

	/**
	 * Tests the load method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoad()
	{
		$query = $this->getMockBuilder('JDatabaseQueryMysqli')
			->disableOriginalConstructor()
			->getMock();

		$this->db->expects($this->once())
			->method('getQuery')
			->with(true)
			->will($this->returnValue($query));

		$query->expects($this->once())
			->method('select')
			->with('*')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('from')
			->with('#__oauth_clients')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('client_id = 5')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$client = array(
			'client_id' => '5',
			'alias' => 'Alias1',
			'key' => 'Key1',
			'secret' => 'Secret1',
			'title' => 'Title1',
			'callback' => 'oob',
			'resource_owner_id' => ''
		);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue($client));

		$this->object->load(5);

		$this->assertEquals($client, TestReflection::getValue($this->object, '_properties'));
	}

	/**
	 * Tests the loadByKey method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadByKey()
	{
		$query = $this->getMockBuilder('JDatabaseQueryMysqli')
			->disableOriginalConstructor()
			->getMock();

		$this->db->expects($this->once())
			->method('getQuery')
			->with(true)
			->will($this->returnValue($query));

		$query->expects($this->once())
			->method('select')
			->with('*')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('from')
			->with('#__oauth_clients')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('`key` = \'Key1\'')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('quoteName')
			->with('key')
			->will($this->returnValue('`key`'));

		$this->db->expects($this->once())
			->method('quote')
			->with('Key1')
			->will($this->returnValue('\'Key1\''));

		$client = array(
			'client_id' => '5',
			'alias' => 'Alias1',
			'key' => 'Key1',
			'secret' => 'Secret1',
			'title' => 'Title1',
			'callback' => 'oob',
			'resource_owner_id' => ''
		);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue($client));

		$this->object->loadByKey('Key1');

		$this->assertEquals($client, TestReflection::getValue($this->object, '_properties'));
	}

	/**
	 * Tests the update method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testUpdate()
	{
		$data = array(
			'client_id' => '5',
			'alias' => 'Alias1',
			'key' => 'Key1',
			'secret' => 'Secret1',
			'title' => 'Title1',
			'callback' => 'oob',
			'resource_owner_id' => ''
		);

		TestReflection::setValue($this->object, '_properties', $data);
		$data['client_id'] = 5;

		$this->db->expects($this->once())
			->method('updateObject')
			->with('#__oauth_clients', (object) $data, 'client_id');

		$this->object->update();
	}

	/**
	 * Tests the update method with a client that isn't in the database.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testUpdateNoClient()
	{
		$data = array(
			'client_id' => 0,
			'alias' => 'Alias1',
			'key' => 'Key1',
			'secret' => 'Secret1',
			'title' => 'Title1',
			'callback' => 'oob',
			'resource_owner_id' => ''
		);

		TestReflection::setValue($this->object, '_properties', $data);

		$this->assertFalse($this->object->update());
	}
}
