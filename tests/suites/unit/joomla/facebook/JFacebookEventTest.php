<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/facebook/http.php';
require_once JPATH_PLATFORM . '/joomla/facebook/facebook.php';
require_once JPATH_PLATFORM . '/joomla/facebook/event.php';

/**
 * Test class for JFacebook.
 * 
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookEventTest extends TestCase
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
	 * @var    JFacebookEvent  Object under test.
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
	 * @var    string  Sample URL string.
	 * @since  12.1
	 */
	protected $sampleUrl = '"https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash2/372662_10575676585_830678637_q.jpg"';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access  protected
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->client = $this->getMock('JFacebookHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JFacebookEvent($this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 * 
	 * @return   void
	 * 
	 * @since   12.1
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the getEvent method
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetEvent()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '1346437213025';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getEvent($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getEvent method - failure
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetEventFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '1346437213025';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getEvent($event, $access_token);
	}

	/**
	 * Tests the getFeed method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetFeed()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/feed?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFeed($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFeed method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetFeedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/feed?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getFeed($event, $access_token);
	}

	/**
	 * Tests the createLink method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateLink()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$link = 'www.example.com';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLink($event, $access_token, $link, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLink method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateLinkFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$link = 'www.example.com';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createLink($event, $access_token, $link, $message);
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
	 * Tests the deleteLink method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteLink()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$link = '156174391080008_235345346';

		$returnData = new stdClass;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($link . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteLink($link, $access_token),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteLink method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteLinkFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$link = '156174391080008_235345346';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($link . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteLink($link, $access_token);
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
	 * Tests the createPost method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreatePost()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252';
		$message = 'message';
		$link = 'www.example.com';
		$picture = 'thumbnail.example.com';
		$name = 'name';
		$caption = 'caption';
		$description = 'description';
		$actions = array('{"name":"Share","link":"http://networkedblogs.com/hGWk3?a=share"}');

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['picture'] = $picture;

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost(
				$event, $access_token, $message, $link, $picture, $name,
				$caption, $description, $actions
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPost method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreatePostFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252';
		$message = 'message';
		$link = 'www.example.com';
		$picture = 'thumbnail.example.com';
		$name = 'name';
		$caption = 'caption';
		$description = 'description';
		$actions = array('{"name":"Share","link":"http://networkedblogs.com/hGWk3?a=share"}');

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['picture'] = $picture;

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createPost(
				$event, $access_token, $message, $link, $picture, $name,
				$caption, $description, $actions
				);
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
	 * Tests the deletePost method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeletePost()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$post = '5148941614_234324';

		$returnData = new stdClass;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($post . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deletePost($post, $access_token),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deletePost method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeletePostFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$post = '5148941614_234324';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($post . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->deletePost($post, $access_token);
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
	 * Tests the createStatus method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateStatus()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createStatus($event, $access_token, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createStatus method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateStatusFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createStatus($event, $access_token, $message);
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
	 * Tests the deleteStatus method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteStatus()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$status = '2457344632_5148941614';

		$returnData = new stdClass;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($status . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteStatus($status, $access_token),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteStatus method - failure.
	 *
	 *@covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteStatusFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$status = '2457344632_5148941614';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($status . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteStatus($status, $access_token);
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
	 * Tests the getInvited method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetInvited()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getInvited($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getInvited method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetInvitedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getInvited($event, $access_token);
	}

	/**
	 * Tests the isInvited method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testIsInvited()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isInvited($event, $access_token, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isInvited method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testIsInvitedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->isInvited($event, $access_token, $user);
	}

	/**
	 * Tests the createInvite method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateInvite()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';
		$users = '23434325456,12343425456';

		// Set POST request parameters.
		$data = array();
		$data['users'] = $users;

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/invited' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createInvite($event, $access_token, $users),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createInvite method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateInviteFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';
		$users = '23434325456,12343425456';

		// Set POST request parameters.
		$data = array();
		$data['users'] = $users;

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/invited' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createInvite($event, $access_token, $users);
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
	 * Tests the deleteInvite method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteInvite()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '2457344632';
		$user = '12467583456';

		$returnData = new stdClass;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($event . '/invited/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteInvite($event, $access_token, $user),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteInvite method - failure.
	 *
	 *@covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteInviteFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '2457344632';
		$user = '12467583456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($event . '/invited/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteInvite($event, $access_token, $user);
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
	 * Tests the getAttending method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetAttending()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAttending($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getAttending method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetAttendingFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getAttending($event, $access_token);
	}

	/**
	 * Tests the createAttending method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateAttending()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/attending' . '?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createAttending($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createAttending method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateAttendingFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/attending' . '?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createAttending($event, $access_token);
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
	 * Tests the isAttending method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testIsAttending()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isAttending($event, $access_token, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isAttending method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testIsAttendingFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->isAttending($event, $access_token, $user);
	}

	/**
	 * Tests the getMaybe method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetMaybe()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMaybe($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMaybe method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetMaybeFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getMaybe($event, $access_token);
	}

	/**
	 * Tests the isMaybe method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testIsMaybe()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isMaybe($event, $access_token, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isMaybe method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testIsMaybeFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->isMaybe($event, $access_token, $user);
	}

	/**
	 * Tests the createMaybe method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateMaybe()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/maybe' . '?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createMaybe($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createMaybe method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateMaybeFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/maybe' . '?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createMaybe($event, $access_token);
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
	 * Tests the getDeclined method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetDeclined()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDeclined($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDeclined method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetDeclinedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getDeclined($event, $access_token);
	}

	/**
	 * Tests the isDeclined method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testIsDeclined()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isDeclined($event, $access_token, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isDeclined method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testIsDeclinedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->isDeclined($event, $access_token, $user);
	}

	/**
	 * Tests the createDeclined method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateDeclined()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/declined' . '?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createDeclined($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createDeclined method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateDeclinedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/declined' . '?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createDeclined($event, $access_token);
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
	 * Tests the getNoreply method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetNoreply()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getNoreply($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getNoreply method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetNoreplyFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getNoreply($event, $access_token);
	}

	/**
	 * Tests the isNoreply method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testIsNoreply()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isNoreply($event, $access_token, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isNoreply method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testIsNoreplyFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply/' . $user . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->isNoreply($event, $access_token, $user);
	}

	/**
	 * Tests the getPicture method.
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetPicture()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$type = 'large';

		$returnData = new JHttpResponse;
		$returnData->headers['Location'] = $this->sampleUrl;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/picture?access_token=' . $access_token . '&type=' . $type)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPicture($event, $access_token, $type),
			$this->equalTo($this->sampleUrl)
		);
	}

	/**
	 * Tests the getPicture method - failure.
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  PHPUnit_Framework_Error
	 */
	public function testGetPictureFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$type = 'large';

		$returnData = new JText($this->errorString);

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/picture?access_token=' . $access_token . '&type=' . $type)
		->will($this->returnValue($returnData));

		$this->object->getPicture($event, $access_token, $type);
	}

	/**
	 * Tests the getPhotos method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetPhotos()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/photos?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPhotos($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPhotos method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetPhotosFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/photos?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getPhotos($event, $access_token);
	}

	/**
	 * Tests the createPhoto method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreatePhoto()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$source = 'path/to/source';
		$message = 'message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data[basename($source)] = '@' . realpath($source);

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/photos' . '?access_token=' . $access_token, $data,
			array('Content-type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPhoto($event, $access_token, $source, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPhoto method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreatePhotoFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$source = '/path/to/source';
		$message = 'message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data[basename($source)] = '@' . realpath($source);

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/photos' . '?access_token=' . $access_token, $data,
			array('Content-type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createPhoto($event, $access_token, $source, $message);
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
	 * Tests the getVideos method.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetVideos()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/videos?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getVideos($event, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getVideos method - failure.
	 * 
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetVideosFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/videos?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getVideos($event, $access_token);
	}

	/**
	 * Tests the createVideo method.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateVideo()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$source = 'path/to/source';
		$title = 'title';
		$description = 'This is a description';

		// Set POST request parameters.
		$data = array();
		$data['title'] = $title;
		$data['description'] = $description;
		$data[basename($source)] = '@' . realpath($source);

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/videos' . '?access_token=' . $access_token, $data,
			array('Content-type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createVideo($event, $access_token, $source, $title, $description),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createVideo method - failure.
	 *
	 * @covers JFacebookObject::sendRequest
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateVideoFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$event = '156174391080008';
		$source = '/path/to/source';
		$title = 'title';
		$description = 'This is a description';

		// Set POST request parameters.
		$data = array();
		$data['title'] = $title;
		$data['description'] = $description;
		$data[basename($source)] = '@' . realpath($source);

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/videos' . '?access_token=' . $access_token, $data,
			array('Content-type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createVideo($event, $access_token, $source, $title, $description);
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
