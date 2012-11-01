<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubHooks.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 * @since       12.3
 */
class JGithubHooksTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JGithubHooks  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"message": "Generic Error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));

		$this->object = new JGithubHooks($this->options, $this->client);
	}

	/**
	 * Tests the create method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreate()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$hook = new stdClass;
		$hook->name = 'acunote';
		$hook->config = array('token' => '123456789');
		$hook->events = array('push', 'public');
		$hook->active = true;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/hooks', json_encode($hook))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create('joomla', 'joomla-platform', 'acunote', array('token' => '123456789'), array('push', 'public')),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreateFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$hook = new stdClass;
		$hook->name = 'acunote';
		$hook->config = array('token' => '123456789');
		$hook->events = array('push', 'public');
		$hook->active = true;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/hooks', json_encode($hook))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->create('joomla', 'joomla-platform', 'acunote', array('token' => '123456789'), array('push', 'public'));
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->message)
			);
		}
		$this->assertTrue($exception);
	}

	/**
	 * Tests the create method - unauthorised event
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  RuntimeException
	 */
	public function testCreateUnauthorisedEvent()
	{
		$this->object->create('joomla', 'joomla-platform', 'acunote', array('token' => '123456789'), array('push', 'faker'));
	}

	/**
	 * Tests the delete method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDelete()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/hooks/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->delete('joomla', 'joomla-platform', 42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the delete method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDeleteFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/hooks/42')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->delete('joomla', 'joomla-platform', 42);
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->message)
			);
		}
		$this->assertTrue($exception);
	}

	/**
	 * Tests the edit method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEdit()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$hook = new stdClass;
		$hook->name = 'acunote';
		$hook->config = array('token' => '123456789');
		$hook->events = array('push', 'public');
		$hook->add_events = array('watch');
		$hook->remove_events = array('watch');
		$hook->active = true;

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/hooks/42', json_encode($hook))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 42, 'acunote', array('token' => '123456789'), array('push', 'public'), array('watch'), array('watch')),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEditFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$hook = new stdClass;
		$hook->name = 'acunote';
		$hook->config = array('token' => '123456789');
		$hook->events = array('push', 'public');
		$hook->add_events = array('watch');
		$hook->remove_events = array('watch');
		$hook->active = true;

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/hooks/42', json_encode($hook))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->edit('joomla', 'joomla-platform', 42, 'acunote', array('token' => '123456789'), array('push', 'public'), array('watch'), array('watch'));
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->message)
			);
		}
		$this->assertTrue($exception);
	}

	/**
	 * Tests the edit method - unauthorised event
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  RuntimeException
	 */
	public function testEditUnauthorisedEvent()
	{
		$this->object->edit('joomla', 'joomla-platform', 42, 'acunote', array('token' => '123456789'), array('push', 'faker'));
	}

	/**
	 * Tests the get method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGet()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/hooks/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get('joomla', 'joomla-platform', 42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the get method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/hooks/42')
			->will($this->returnValue($returnData));

		$this->object->get('joomla', 'joomla-platform', 42);
	}

	/**
	 * Tests the getList method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetList()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/hooks')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getList('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetListFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/hooks')
			->will($this->returnValue($returnData));

		$this->object->getList('joomla', 'joomla-platform');
	}

	/**
	 * Tests the test method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testTest()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/hooks/42/test')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->test('joomla', 'joomla-platform', 42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the test method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testTestFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/hooks/42/test')
			->will($this->returnValue($returnData));

		$this->object->test('joomla', 'joomla-platform', 42);
	}
}
