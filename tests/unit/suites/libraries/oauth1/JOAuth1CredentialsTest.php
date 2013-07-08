<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Credentials.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1CredentialsTest extends TestCase
{
	/**
	 * The test object.
	 * @var JOAuth1Credentials
	 */
	protected $object;

	/**
	 * The table object.
	 * @var JOAuth1TableCredentials
	 */
	protected $table;

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
			->getMock();

		$this->object = new JOAuth1Credentials($this->db);
	}

	/**
	 * Tests the constructor.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'JOAuth1CredentialsStateNew',
			TestReflection::getValue($this->object, '_state')
		);
	}

	/**
	 * Tests the authorise method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAuthorise()
	{
		$state = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		$newState = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		TestReflection::setValue($this->object, '_state', $state);

		$state->expects($this->once())
			->method('authorise')
			->with(5)
			->will($this->returnValue($newState));

		$this->object->authorise(5);

		$this->assertSame($newState, TestReflection::getValue($this->object, '_state'));
	}

	/**
	 * Tests the convert method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testConvert()
	{
		$state = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		$newState = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		TestReflection::setValue($this->object, '_state', $state);

		$state->expects($this->once())
			->method('convert')
			->with()
			->will($this->returnValue($newState));

		$this->object->convert();

		$this->assertSame($newState, TestReflection::getValue($this->object, '_state'));
	}

	/**
	 * Tests the deny method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDeny()
	{
		$state = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		$newState = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		TestReflection::setValue($this->object, '_state', $state);

		$state->expects($this->once())
			->method('deny')
			->with()
			->will($this->returnValue($newState));

		$this->object->deny();

		$this->assertSame($newState, TestReflection::getValue($this->object, '_state'));
	}

	/**
	 * Tests the initialise method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testInitialise()
	{
		$state = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		$newState = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		TestReflection::setValue($this->object, '_state', $state);

		$state->expects($this->once())
			->method('initialise')
			->with('ClientKey', 'CallbackUrl')
			->will($this->returnValue($newState));

		$this->object->initialise('ClientKey', 'CallbackUrl');

		$this->assertSame($newState, TestReflection::getValue($this->object, '_state'));
	}

	/**
	 * Tests the revoke method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRevoke()
	{
		$state = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		$newState = $this->getMockBuilder('JOAuth1CredentialsStateNew')
			->disableOriginalConstructor()
			->getMock();

		TestReflection::setValue($this->object, '_state', $state);

		$state->expects($this->once())
			->method('revoke')
			->with()
			->will($this->returnValue($newState));

		$this->object->revoke();

		$this->assertSame($newState, TestReflection::getValue($this->object, '_state'));
	}

	/**
	 * Tests the load method with temporary credentials.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadTemporary()
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
			->with('#__oauth_credentials')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('`key` = \'Key1\'')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('quoteName')
			->with('key')
			->will($this->returnValue('`key`'));

		$this->db->expects($this->once())
			->method('quote')
			->with('Key1')
			->will($this->returnValue('\'Key1\''));

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue(Array(
				'type' => JOAuth1Credentials::TEMPORARY,
				'callback_url' => 'oob',
				'client_key' => 'key1',
				'key' => 'Key1',
				'resource_owner_id' => '125'
			)));

		$this->object->load('Key1');

		$state = TestReflection::getValue($this->object, '_state');

		$this->assertEquals(JOAuth1Credentials::TEMPORARY, $state->type);
		$this->assertEquals('oob', $state->callback_url);
		$this->assertEquals('key1', $state->client_key);
		$this->assertEquals('Key1', $state->key);
		$this->assertEquals('125', $state->resource_owner_id);
	}

	/**
	 * Tests the load method with empty properties.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadEmptyProperties()
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
			->with('#__oauth_credentials')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('`key` = \'Key1\'')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('quoteName')
			->with('key')
			->will($this->returnValue('`key`'));

		$this->db->expects($this->once())
			->method('quote')
			->with('Key1')
			->will($this->returnValue('\'Key1\''));

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue(Array()));

		$this->object->load('Key1');

		$state = TestReflection::getValue($this->object, '_state');

		$this->assertEquals(JOAuth1Credentials::TEMPORARY, $state->type);
	}

	/**
	 * Tests the load method with authorised credentials.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadAuthorised()
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
			->with('#__oauth_credentials')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('`key` = \'Key1\'')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('quoteName')
			->with('key')
			->will($this->returnValue('`key`'));

		$this->db->expects($this->once())
			->method('quote')
			->with('Key1')
			->will($this->returnValue('\'Key1\''));

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue(Array(
				'type' => JOAuth1Credentials::AUTHORISED,
				'callback_url' => 'oob',
				'client_key' => 'key1',
				'key' => 'Key1',
				'resource_owner_id' => '125'
			)));

		$this->object->load('Key1');

		$state = TestReflection::getValue($this->object, '_state');

		$this->assertEquals(JOAuth1Credentials::AUTHORISED, $state->type);
		$this->assertEquals('oob', $state->callback_url);
		$this->assertEquals('key1', $state->client_key);
		$this->assertEquals('Key1', $state->key);
		$this->assertEquals('125', $state->resource_owner_id);
	}

	/**
	 * Tests the load method with token credentials.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadToken()
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
			->with('#__oauth_credentials')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('`key` = \'Key1\'')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('quoteName')
			->with('key')
			->will($this->returnValue('`key`'));

		$this->db->expects($this->once())
			->method('quote')
			->with('Key1')
			->will($this->returnValue('\'Key1\''));

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue(Array(
				'type' => JOAuth1Credentials::TOKEN,
				'callback_url' => 'oob',
				'client_key' => 'key1',
				'key' => 'Key1',
				'resource_owner_id' => '125'
			)));

		$this->object->load('Key1');

		$state = TestReflection::getValue($this->object, '_state');

		$this->assertEquals(JOAuth1Credentials::TOKEN, $state->type);
		$this->assertEquals('oob', $state->callback_url);
		$this->assertEquals('key1', $state->client_key);
		$this->assertEquals('Key1', $state->key);
		$this->assertEquals('125', $state->resource_owner_id);
	}

	/**
	 * Tests the load method with token credentials.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testLoadInvalid()
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
			->with('#__oauth_credentials')
			->will($this->returnSelf());

		$query->expects($this->once())
			->method('where')
			->with('`key` = \'Key1\'')
			->will($this->returnSelf());

		$this->db->expects($this->once())
			->method('quoteName')
			->with('key')
			->will($this->returnValue('`key`'));

		$this->db->expects($this->once())
			->method('quote')
			->with('Key1')
			->will($this->returnValue('\'Key1\''));

		$this->db->expects($this->once())
			->method('setQuery')
			->with($query);

		$this->db->expects($this->once())
			->method('loadAssoc')
			->will($this->returnValue(Array(
				'type' => 5,
				'callback_url' => 'oob',
				'client_key' => 'key1',
				'key' => 'Key1',
				'resource_owner_id' => '125'
			)));

		$this->object->load('Key1');
	}
}
