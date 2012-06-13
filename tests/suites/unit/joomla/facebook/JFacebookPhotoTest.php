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
require_once JPATH_PLATFORM . '/joomla/facebook/photo.php';

/**
 * Test class for JFacebook.
 * 
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookPhotoTest extends TestCase
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
	 * @var    JFacebookPhoto  Object under test.
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

		$this->object = new JFacebookPhoto($this->options, $this->client);
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
	 * Tests the getPhoto method
	 * 
	 * @covers JFacebookPhoto::getPhoto
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetPhoto()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPhoto($photo, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPhoto method - failure
	 * 
	 * @covers JFacebookPhoto::getPhoto
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetPhotoFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getPhoto($photo, $access_token);
	}

	/**
	 * Tests the getComments method
	 * 
	 * @covers JFacebookPhoto::getComments
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetComments()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/comments?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComments($photo, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComments method - failure
	 * 
	 * @covers JFacebookPhoto::getComments
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetCommentsFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/comments?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getComments($photo, $access_token);
	}

	/**
	 * Tests the createComment method.
	 *
	 * @covers JFacebookPhoto::createComment
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateComment()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';
		$message = 'test message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/comments?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createComment($photo, $access_token, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createComment method - failure.
	 *
	 * @covers JFacebookPhoto::createComment
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateCommentFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';
		$message = 'test message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/comments?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createComment($photo, $access_token, $message);
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
	 * Tests the deleteComment method.
	 * 
	 * @covers JFacebookPhoto::deleteComment
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteComment()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$comment = '5148941614_12343468';

		$returnData = new stdClass;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteComment($comment, $access_token),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteComment method - failure.
	 *
	 * @covers JFacebookPhoto::deleteComment
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteCommentFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$comment = '5148941614_12343468';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($comment . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteComment($comment, $access_token);
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
	 * Tests the getLikes method
	 * 
	 * @covers JFacebookPhoto::getLikes
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetLikes()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/likes?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLikes($photo, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLikes method - failure
	 * 
	 * @covers JFacebookPhoto::getLikes
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetLikesFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/likes?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getLikes($photo, $access_token);
	}

	/**
	 * Tests the createLike method.
	 *
	 * @covers JFacebookPhoto::createLike
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateLike()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/likes?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLike($photo, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLike method - failure.
	 *
	 * @covers JFacebookPhoto::createLike
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateLikeFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/likes?access_token=' . $access_token, '')
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createLike($photo, $access_token);
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
	 * Tests the deleteLike method.
	 * 
	 * @covers JFacebookPhoto::deleteLike
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteLike()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = true;

		$this->client->expects($this->once())
		->method('delete')
		->with($photo . '/likes?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteLike($photo, $access_token),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the deleteLike method - failure.
	 *
	 * @covers JFacebookPhoto::deleteLike
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testDeleteLikeFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with($photo . '/likes?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteLike($photo, $access_token);
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
	 * Tests the getTags method
	 * 
	 * @covers JFacebookPhoto::getTags
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetTags()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/tags?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getTags($photo, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getTags method - failure
	 * 
	 * @covers JFacebookPhoto::getTags
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetTagsFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/tags?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getTags($photo, $access_token);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
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
	 * @covers JFacebookPhoto::createTag
	 * @dataProvider  seedCreateTag
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateTag($to)
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
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
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createTag($photo, $access_token, $to, $tag_text, $x, $y),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createTag method - failure.
	 * 
	 * @param   mixed  $to  ID of the User or an array of Users to tag in the photo.
	 * 
	 * @covers JFacebookPhoto::createTag
	 * @dataProvider  seedCreateTag
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateTagFailure($to)
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
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
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createTag($photo, $access_token, $to, $tag_text, $x, $y);
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
	 * Tests the updateTag method.
	 * 
	 * @covers JFacebookPhoto::updateTag
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testUpdateTag()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
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
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateTag($photo, $access_token, $to, $x, $y),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateTag method - failure.
	 *
	 * @covers JFacebookPhoto::updateTag
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testUpdateTagFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
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
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($photo . '/tags?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->updateTag($photo, $access_token, $to, $x, $y);
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
	 * Tests the getPicture method
	 * 
	 * @covers JFacebookPhoto::getPicture
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetPicture()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/picture?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPicture($photo, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPicture method - failure
	 * 
	 * @covers JFacebookPhoto::getPicture
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetPictureFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$photo = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($photo . '/picture?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getPicture($photo, $access_token);
	}
}
