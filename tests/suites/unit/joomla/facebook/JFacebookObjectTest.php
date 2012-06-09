<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/facebook/http.php';
require_once JPATH_PLATFORM . '/joomla/facebook/object.php';
require_once __DIR__ . '/stubs/JFacebookObjectMock.php';

/**
 * Test class for JFacebook.
 * 
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookObjectTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  Mock client object.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * @var    JFacebookObjectMock  Object under test.
	 * @since  12.1
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.1
	 */
	protected $errorString = '{"error": {"message": "Generic Error."}}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 * 
	 * @return   void
	 * 
	 * @since    12.1
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->client = $this->getMock('JFacebookHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JFacebookObjectMock($this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 * 
	 * @return   void
	 * 
	 * @since    12.1
	 */
	protected function tearDown()
	{
	}

	/**
	 * Test the fetchUrl method.
	 * 
	 * @return  void
	 * 
	 * @since    12.1
	 */
	public function testFetchUrl()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the sendRequest method.
	 *
	 * @return  void
	 * 
	 * @covers JFacebookObject::fetchUrl()
	 * @since    12.1
	 */
	public function testSendRequest()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the get method
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGet()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($object . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get($object, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the get method - failure
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($object . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->get($object, $access_token);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedGetConnection()
	{
		// Extra fields for the request URL.
		return array(
			array('&type=large'),
			array(''),
		);
	}

	/**
	 * Tests the getConnection method
	 * 
	 * @param   string  $extra_fields  Extra fields for the request URL.
	 * 
	 * @covers JFacebookObject::sendRequest
	 * @dataProvider  seedDeleteConnection
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetConnection($extra_fields)
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '124346363456';
		$connection = 'picture';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($object . '/' . $connection . '?access_token=' . $access_token . $extra_fields)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getConnection($object, $access_token, $connection, $extra_fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getConnection method - failure
	 * 
	  * @param   string  $extra_fields  Extra fields for the request URL.
	 * 
	 * @covers JFacebookObject::sendRequest
	 * @dataProvider  seedDeleteConnection
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetConnectionFailure($extra_fields)
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '124346363456';
		$connection = 'picture';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($object . '/' . $connection . '?access_token=' . $access_token . $extra_fields)
		->will($this->returnValue($returnData));

		$this->object->getConnection($object, $access_token, $connection, $extra_fields);
	}

	/**
	 * Tests the createConnection method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateConnection()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '124346363456';
		$connection = 'comments';
		$parameters['message'] = 'test message';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($object . '/' . $connection . '?access_token=' . $access_token, $parameters)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createConnection($object, $access_token, $connection, $parameters),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createConnection method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateConnectionFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '124346363456';
		$connection = 'comments';
		$parameters['message'] = 'test message';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($object . '/' . $connection . '?access_token=' . $access_token, $parameters)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createConnection($object, $access_token, $connection, $parameters);
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->error->message)
			);
		}
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedDeleteConnection()
	{
		// Connection
		return array(
			array('likes'),
			array(''),
		);
	}

	/**
	 * Tests the deleteConnection method.
	 * 
	 * @param   string  $connection  Connection to test.
	 * 
	 * @covers JFacebookObject::sendRequest
	 * @dataProvider  seedDeleteConnection
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteConnection($connection)
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '5148941614_12343468';

		$returnData = new stdClass;
		$returnData->body = true;

		if ($connection != null)
		{
			$this->client->expects($this->once())
			->method('delete')
			->with($object . '/' . $connection . '?access_token=' . $access_token)
			->will($this->returnValue($returnData));
		}
		else
		{
			$this->client->expects($this->once())
			->method('delete')
			->with($object . '?access_token=' . $access_token)
			->will($this->returnValue($returnData));
		}

		$this->assertThat(
			$this->object->deleteConnection($object, $access_token, $connection),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteConnection method - failure.
	 *
	 * @param   string  $connection  Connection to test.
	 *
	 * @covers JFacebookObject::sendRequest
	 * @dataProvider  seedDeleteConnection
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteConnectionFailure($connection)
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$object = '5148941614_12343468';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		if ($connection != null)
		{
			$this->client->expects($this->once())
			->method('delete')
			->with($object . '/' . $connection . '?access_token=' . $access_token)
			->will($this->returnValue($returnData));
		}
		else
		{
			$this->client->expects($this->once())
			->method('delete')
			->with($object . '?access_token=' . $access_token)
			->will($this->returnValue($returnData));
		}

		try
		{
			$this->object->deleteConnection($object, $access_token, $connection);
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->error->message)
			);
		}
	}
}
