<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFacebookGroup.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @since       13.1
 */
class JFacebookGroupTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JFacebookGroup  Object under test.
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
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access  protected
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$app_id = "app_id";
		$app_secret = "app_secret";
		$my_url = "http://localhost/gsoc/joomla-platform/facebook_test.php";
		$access_token = array(
			'access_token' => 'token',
			'expires' => '51837673', 'created' => '2443672521');

		$this->options = new JRegistry;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->input = new JInput;
		$this->oauth = new JFacebookOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JFacebookGroup($this->options, $this->client, $this->oauth);

		$this->options->set('clientid', $app_id);
		$this->options->set('clientsecret', $app_secret);
		$this->options->set('redirecturi', $my_url);
		$this->options->set('sendheaders', true);
		$this->options->set('authmethod', 'get');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return   void
	 *
	 * @since   13.1
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the getGroup method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetGroup()
	{
		$token = $this->oauth->getToken();
		$group = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getGroup($group),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getGroup method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetGroupFailure()
	{
		$token = $this->oauth->getToken();
		$group = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getGroup($group);
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
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/feed?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFeed($group),
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
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/feed?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getFeed($group);
	}

	/**
	 * Tests the getMembers method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetMembers()
	{
		$token = $this->oauth->getToken();
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/members?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMembers($group),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMembers method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetMembersFailure()
	{
		$token = $this->oauth->getToken();
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/members?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getMembers($group);
	}

	/**
	 * Tests the getDocs method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetDocs()
	{
		$token = $this->oauth->getToken();
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/docs?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDocs($group),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDocs method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetDocsFailure()
	{
		$token = $this->oauth->getToken();
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/docs?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getDocs($group);
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
		$group = '156174391080008';
		$type = 'large';

		$returnData = new JHttpResponse;
		$returnData->code = 302;
		$returnData->headers['Location'] = $this->sampleUrl;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/picture?type=' . $type . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPicture($group, $type),
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
		$group = '156174391080008';
		$type = 'large';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/picture?type=' . $type . '&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPicture($group, $type);
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
		$group = '156174391080008';
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
		->with($group . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLink($group, $link, $message),
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
		$exception = false;
		$token = $this->oauth->getToken();
		$group = '156174391080008';
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
		->with($group . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createLink($group, $link, $message);
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
		$exception = false;
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
		$group = '134534252';
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
		->with($group . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost(
				$group, $message, $link, $picture, $name,
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
		$group = '134534252';
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
		->with($group . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createPost(
			$group, $message, $link, $picture, $name,
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
		$exception = false;
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
		$group = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($group . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createStatus($group, $message),
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
		$group = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($group . '/feed' . '?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createStatus($group, $message);
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
		$exception = false;
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
}
