<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

/**
 * Test class for JFacebookComment.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * @since       13.1
 */
class JFacebookCommentTest extends TestCase
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
	 * @var    JFacebookComment  Object under test.
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

		$this->object = new JFacebookComment($this->options, $this->client, $this->oauth);

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
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->options, $this->client, $this->input, $this->oauth, $this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getComment method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetComment()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with($comment . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComment($comment),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComment method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetCommentFailure()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with($comment . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getComment($comment);
	}

	/**
	 * Tests the deleteComment method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteComment()
	{
		$comment = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteComment($comment),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteComment method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testDeleteCommentFailure()
	{
		$comment = '5148941614';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteComment($comment);
	}

	/**
	 * Tests the getComments method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetComments()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with($comment . '/comments?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComments($comment),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComments method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetCommentsFailure()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with($comment . '/comments?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getComments($comment);
	}

	/**
	 * Tests the createComment method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateComment()
	{
		$comment = '124346363456';
		$message = 'test message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($comment . '/comments?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createComment($comment, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createComment method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateCommentFailure()
	{
		$comment = '124346363456';
		$message = 'test message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($comment . '/comments?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createComment($comment, $message);
	}

	/**
	 * Tests the getLikes method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetLikes()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with($comment . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLikes($comment),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLikes method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetLikesFailure()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('get')
		->with($comment . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getLikes($comment);
	}

	/**
	 * Tests the createLike method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateLike()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($comment . '/likes?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLike($comment),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLike method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateLikeFailure()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('post')
		->with($comment . '/likes?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->object->createLike($comment);
	}

	/**
	 * Tests the deleteLike method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteLike()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteLike($comment),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteLike method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testDeleteLikeFailure()
	{
		$comment = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$token = $this->oauth->getToken();

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteLike($comment);
	}
}
