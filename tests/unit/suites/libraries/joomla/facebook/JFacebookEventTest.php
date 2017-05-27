<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

/**
 * Test class for JFacebookEvent.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * @since       13.1
 */
class JFacebookEventTest extends TestCase
{
	/**
	 * @var    Registry  Options for the Facebook object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JFacebookEvent  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    JFacebookOauth  Facebook OAuth 2 client
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  13.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  13.1
	 */
	protected $errorString = '{"error": {"message": "Generic Error."}}';

	/**
	 * @var    string  Sample URL string.
	 * @since  13.1
	 */
	protected $sampleUrl = '"https://fbcdn-profile-a.akamaihd.net/hprofile-ak-ash2/372662_10575676585_830678637_q.jpg"';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$app_id = "app_id";
		$app_secret = "app_secret";
		$my_url = "http://localhost/gsoc/joomla-platform/facebook_test.php";
		$access_token = array(
			'access_token' => 'token',
			'expires' => '51837673',
			'created' => '2443672521'
		);

		$this->options = new Registry;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();
		$this->input = new JInput;
		$this->oauth = new JFacebookOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JFacebookEvent($this->options, $this->client, $this->oauth);

		$this->options->set('clientid', $app_id);
		$this->options->set('clientsecret', $app_secret);
		$this->options->set('redirecturi', $my_url);
		$this->options->set('sendheaders', true);
		$this->options->set('authmethod', 'get');

		parent::setUp();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->client);
		unset($this->input);
		unset($this->oauth);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getEvent method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetEvent()
	{
		$token = $this->oauth->getToken();
		$event = '1346437213025';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getEvent($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getEvent method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetEventFailure()
	{
		$token = $this->oauth->getToken();
		$event = '1346437213025';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getEvent($event);
	}

	/**
	 * Tests the getFeed method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFeed()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/feed?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFeed($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFeed method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetFeedFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/feed?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getFeed($event);
	}

	/**
	 * Tests the createLink method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateLink()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$link = 'www.example.com';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLink($event, $link, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLink method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateLinkFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$link = 'www.example.com';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createLink($event, $link, $message);
	}

	/**
	 * Tests the deleteLink method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteLink()
	{
		$token = $this->oauth->getToken();
		$link = '156174391080008_235345346';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($link . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteLink($link),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteLink method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testDeleteLinkFailure()
	{
		$token = $this->oauth->getToken();
		$link = '156174391080008_235345346';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($link . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteLink($link);
	}

	/**
	 * Tests the createPost method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreatePost()
	{
		$token = $this->oauth->getToken();
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
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost(
				$event, $message, $link, $picture, $name,
				$caption, $description, $actions
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPost method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreatePostFailure()
	{
		$token = $this->oauth->getToken();
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
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createPost(
				$event, $message, $link, $picture, $name,
				$caption, $description, $actions
				);
	}

	/**
	 * Tests the deletePost method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeletePost()
	{
		$token = $this->oauth->getToken();
		$post = '5148941614_234324';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($post . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deletePost($post),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deletePost method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testDeletePostFailure()
	{
		$token = $this->oauth->getToken();
		$post = '5148941614_234324';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($post . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deletePost($post);
	}

	/**
	 * Tests the createStatus method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateStatus()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createStatus($event, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createStatus method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateStatusFailure()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createStatus($event, $message);
	}

	/**
	 * Tests the deleteStatus method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteStatus()
	{
		$token = $this->oauth->getToken();
		$status = '2457344632_5148941614';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($status . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteStatus($status),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteStatus method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testDeleteStatusFailure()
	{
		$token = $this->oauth->getToken();
		$status = '2457344632_5148941614';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($status . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteStatus($status);
	}

	/**
	 * Tests the getInvited method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetInvited()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getInvited($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getInvited method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetInvitedFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getInvited($event);
	}

	/**
	 * Tests the isInvited method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testIsInvited()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isInvited($event, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isInvited method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testIsInvitedFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/invited/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->isInvited($event, $user);
	}

	/**
	 * Tests the createInvite method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateInvite()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';
		$users = '23434325456,12343425456';

		// Set POST request parameters.
		$data = array();
		$data['users'] = $users;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/invited' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createInvite($event, $users),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createInvite method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateInviteFailure()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';
		$users = '23434325456,12343425456';

		// Set POST request parameters.
		$data = array();
		$data['users'] = $users;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/invited' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createInvite($event, $users);
	}

	/**
	 * Tests the deleteInvite method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteInvite()
	{
		$token = $this->oauth->getToken();
		$event = '2457344632';
		$user = '12467583456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($event . '/invited/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteInvite($event, $user),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteInvite method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testDeleteInviteFailure()
	{
		$token = $this->oauth->getToken();
		$event = '2457344632';
		$user = '12467583456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($event . '/invited/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteInvite($event, $user);
	}

	/**
	 * Tests the getAttending method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetAttending()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAttending($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getAttending method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetAttendingFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getAttending($event);
	}

	/**
	 * Tests the createAttending method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateAttending()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/attending' . '?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createAttending($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createAttending method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateAttendingFailure()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/attending' . '?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->object->createAttending($event);
	}

	/**
	 * Tests the isAttending method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testIsAttending()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isAttending($event, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isAttending method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testIsAttendingFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/attending/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->isAttending($event, $user);
	}

	/**
	 * Tests the getMaybe method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetMaybe()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMaybe($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMaybe method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetMaybeFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getMaybe($event);
	}

	/**
	 * Tests the isMaybe method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testIsMaybe()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isMaybe($event, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isMaybe method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testIsMaybeFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/maybe/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->isMaybe($event, $user);
	}

	/**
	 * Tests the createMaybe method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateMaybe()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/maybe' . '?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createMaybe($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createMaybe method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateMaybeFailure()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/maybe' . '?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->object->createMaybe($event);
	}

	/**
	 * Tests the getDeclined method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetDeclined()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDeclined($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDeclined method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetDeclinedFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getDeclined($event);
	}

	/**
	 * Tests the isDeclined method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testIsDeclined()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isDeclined($event, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isDeclined method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testIsDeclinedFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/declined/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->isDeclined($event, $user);
	}

	/**
	 * Tests the createDeclined method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateDeclined()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/declined' . '?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createDeclined($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createDeclined method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateDeclinedFailure()
	{
		$token = $this->oauth->getToken();
		$event = '134534252457';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($event . '/declined' . '?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->object->createDeclined($event);
	}

	/**
	 * Tests the getNoreply method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetNoreply()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getNoreply($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getNoreply method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetNoreplyFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getNoreply($event);
	}

	/**
	 * Tests the isNoreply method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testIsNoreply()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isNoreply($event, $user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isNoreply method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testIsNoreplyFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$user = '2356736745787';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/noreply/' . $user . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->isNoreply($event, $user);
	}

	/**
	 * Tests the getPicture method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPicture()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$type = 'large';

		$returnData = new JHttpResponse;
		$returnData->code = 302;
		$returnData->headers['Location'] = $this->sampleUrl;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/picture?redirect=false&type=' . $type . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPicture($event, false, $type),
			$this->equalTo($this->sampleUrl)
		);
	}

	/**
	 * Tests the getPicture method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPictureFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$type = 'large';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/picture?redirect=false&type=' . $type . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPicture($event, false, $type);
	}

	/**
	 * Tests the getPhotos method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPhotos()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/photos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPhotos($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPhotos method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPhotosFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/photos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPhotos($event);
	}

	/**
	 * Tests the createPhoto method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreatePhoto()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$source = 'path/to/source';
		$message = 'message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data[basename($source)] = '@' . realpath($source);

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/photos' . '?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPhoto($event, $source, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPhoto method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreatePhotoFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';
		$source = '/path/to/source';
		$message = 'message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data[basename($source)] = '@' . realpath($source);

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/photos' . '?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->object->createPhoto($event, $source, $message);
	}

	/**
	 * Tests the getVideos method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetVideos()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/videos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getVideos($event),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getVideos method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetVideosFailure()
	{
		$token = $this->oauth->getToken();
		$event = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($event . '/videos?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getVideos($event);
	}

	/**
	 * Tests the createVideo method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateVideo()
	{
		$token = $this->oauth->getToken();
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
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/videos' . '?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createVideo($event, $source, $title, $description),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createVideo method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateVideoFailure()
	{
		$token = $this->oauth->getToken();
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
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with(
			$event . '/videos' . '?access_token=' . $token['access_token'], $data,
			array('Content-Type' => 'multipart/form-data')
			)
		->will($this->returnValue($returnData));

		$this->object->createVideo($event, $source, $title, $description);
	}
}
