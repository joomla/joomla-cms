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
 * Test class for JFacebookPhoto.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * @since       13.1
 */
class JFacebookPhotoTest extends TestCase
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
	 * @var    JFacebookPhoto  Object under test.
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
			'expires' => '51837673', 'created' => '2443672521');

		$this->options = new Registry;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();
		$this->input = new JInput;
		$this->oauth = new JFacebookOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JFacebookPhoto($this->options, $this->client, $this->oauth);

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
	 * Tests the getPhoto method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPhoto()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPhoto($photo),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPhoto method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPhotoFailure()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPhoto($photo);
	}

	/**
	 * Tests the getComments method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetComments()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/comments?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComments($photo),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComments method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @expectedException  RuntimeException
	 */
	public function testGetCommentsFailure()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/comments?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getComments($photo);
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';
		$message = 'test message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/comments?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createComment($photo, $message),
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';
		$message = 'test message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/comments?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createComment($photo, $message);
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
		$token = $this->oauth->getToken();
		$comment = '5148941614_12343468';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

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
		$token = $this->oauth->getToken();
		$comment = '5148941614_12343468';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteComment($comment);
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLikes($photo),
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getLikes($photo);
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/likes?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLike($photo),
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/likes?access_token=' . $token['access_token'], '')
		->will($this->returnValue($returnData));

		$this->object->createLike($photo);
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($photo . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteLike($photo),
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
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($photo . '/likes?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->deleteLike($photo);
	}

	/**
	 * Tests the getTags method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetTags()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/tags?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getTags($photo),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getTags method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetTagsFailure()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/tags?access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getTags($photo);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 13.1
	*/
	public function seedCreateTag()
	{
		// User_id
		return array(
			array('34653423123'),
			array(array('{"id":"1234"}', '{"id":"12345"}'))
		);
	}

	/**
	 * Tests the createTag method.
	 *
	 * @param   mixed  $to  ID of the User or an array of Users to tag in the photo.
	 *
	 * @dataProvider  seedCreateTag
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateTag($to)
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';
		$tag_text = 'tag text';
		$x = 12;
		$y = 65;

		// Set POST request parameters.
		$data = array();
		$data['tag_text'] = $tag_text;
		$data['x'] = $x;
		$data['y'] = $y;

		if (is_array($to))
		{
			$data['tags'] = $to;
		}
		else
		{
			$data['to'] = $to;
		}

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createTag($photo, $to, $tag_text, $x, $y),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createTag method - failure.
	 *
	 * @param   mixed  $to  ID of the User or an array of Users to tag in the photo.
	 *
	 * @dataProvider  seedCreateTag
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testCreateTagFailure($to)
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';
		$tag_text = 'tag text';
		$x = 12;
		$y = 65;

		// Set POST request parameters.
		$data = array();
		$data['tag_text'] = $tag_text;
		$data['x'] = $x;
		$data['y'] = $y;

		if (is_array($to))
		{
			$data['tags'] = $to;
		}
		else
		{
			$data['to'] = $to;
		}

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->createTag($photo, $to, $tag_text, $x, $y);
	}

	/**
	 * Tests the updateTag method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUpdateTag()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';
		$to = '113467457834';
		$x = 12;
		$y = 65;

		// Set POST request parameters.
		$data = array();
		$data['to'] = $to;
		$data['x'] = $x;
		$data['y'] = $y;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateTag($photo, $to, $x, $y),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateTag method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testUpdateTagFailure()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';
		$to = '113467457834';
		$x = 12;
		$y = 65;

		// Set POST request parameters.
		$data = array();
		$data['to'] = $to;
		$data['x'] = $x;
		$data['y'] = $y;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $token['access_token'], $data)
		->will($this->returnValue($returnData));

		$this->object->updateTag($photo, $to, $x, $y);
	}

	/**
	 * Tests the getPicture method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetPicture()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/picture?redirect=false&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPicture($photo, false),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPicture method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  RuntimeException
	 */
	public function testGetPictureFailure()
	{
		$token = $this->oauth->getToken();
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/picture?redirect=false&access_token=' . $token['access_token'])
		->will($this->returnValue($returnData));

		$this->object->getPicture($photo, false);
	}
}
