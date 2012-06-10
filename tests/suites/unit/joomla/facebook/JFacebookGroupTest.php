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
require_once JPATH_PLATFORM . '/joomla/facebook/group.php';

/**
 * Test class for JFacebook.
 * 
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookGroupTest extends TestCase
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
	 * @var    JFacebookGroup  Object under test.
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

		$this->object = new JFacebookGroup($this->options, $this->client);
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
	 * Tests the getGroup method
	 * 
	 * @covers JFacebookGroup::getGroup
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetGroup()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getGroup($group, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getGroup method - failure
	 * 
	 * @covers JFacebookGroup::getGroup
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetGroupFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '124346363456';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getGroup($group, $access_token);
	}

	/**
	 * Tests the getFeed method.
	 * 
	 * @covers JFacebookGroup::getFeed
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetFeed()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/feed?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFeed($group, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFeed method - failure.
	 * 
	 * @covers JFacebookGroup::getFeed
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetFeedFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/feed?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getFeed($group, $access_token);
	}

	/**
	 * Tests the getMembers method.
	 * 
	 * @covers JFacebookGroup::getMembers
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetMembers()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/members?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMembers($group, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMembers method - failure.
	 * 
	 * @covers JFacebookGroup::getMembers
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetMembersFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/members?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getMembers($group, $access_token);
	}

	/**
	 * Tests the getDocs method.
	 * 
	 * @covers JFacebookGroup::getDocs
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetDocs()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/docs?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDocs($group, $access_token),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDocs method - failure.
	 * 
	 * @covers JFacebookGroup::getDocs
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  DomainException
	 */
	public function testGetDocsFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with($group . '/docs?access_token=' . $access_token)
		->will($this->returnValue($returnData));

		$this->object->getDocs($group, $access_token);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedGetPicture()
	{
		// Extra fields for the request URL.
		return array(
			array('&type=large'),
			array(null),
		);
	}

	/**
	 * Tests the getPicture method.
	 *
	 * @param   string  $type  Extra fields for the request URL.
	 *
	 * @covers JFacebookGroup::getPicture
	 * @dataProvider  seedGetPicture
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testGetPicture($type)
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new JHttpResponse;
		$returnData->headers['Location'] = $this->sampleUrl;

		if ($type != null)
		{
			$this->client->expects($this->once())
			->method('get')
			->with($group . '/picture?access_token=' . $access_token . '&type=' . $type)
			->will($this->returnValue($returnData));
		}
		else
		{
			$this->client->expects($this->once())
			->method('get')
			->with($group . '/picture?access_token=' . $access_token)
			->will($this->returnValue($returnData));
		}

		$this->assertThat(
			$this->object->getPicture($group, $access_token, $type),
			$this->equalTo($this->sampleUrl)
		);
	}

	/**
	 * Tests the getPicture method - failure.
	 *
	 * @param   string  $type  Extra fields for the request URL.
	 *
	 * @covers JFacebookGroup::getPicture
	 * @dataProvider  seedGetPicture
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 * @expectedException  PHPUnit_Framework_Error
	 */
	public function testGetPictureFailure($type)
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';

		$returnData = new JText($this->errorString);

		if ($type != null)
		{
			$this->client->expects($this->once())
			->method('get')
			->with($group . '/picture?access_token=' . $access_token . '&type=' . $type)
			->will($this->returnValue($returnData));
		}
		else
		{
			$this->client->expects($this->once())
			->method('get')
			->with($group . '/picture?access_token=' . $access_token)
			->will($this->returnValue($returnData));
		}

		$this->object->getPicture($group, $access_token, $type);
	}

	/**
	 * Tests the createLink method.
	 *
	 * @covers JFacebookGroup::createLink
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateLink()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';
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
		->with($group . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createLink($group, $access_token, $link, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLink method - failure.
	 *
	 * @covers JFacebookGroup::createLink
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateLinkFailure()
	{
		$exception = false;
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '156174391080008';
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
		->with($group . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createLink($group, $access_token, $link, $message);
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
	 * @covers JFacebookGroup::deleteLink
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
	 * @covers JFacebookGroup::deleteLink
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
	 * @covers JFacebookGroup::createPost
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreatePost()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
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
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($group . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPost(
				$group, $access_token, $message, $link, $picture, $name,
				$caption, $description, $actions
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPost method - failure.
	 *
	 * @covers JFacebookGroup::createPost
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreatePostFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
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
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($group . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createPost(
				$group, $access_token, $message, $link, $picture, $name,
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
	 * @covers JFacebookGroup::deletePost
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
	 * @covers JFacebookGroup::deletePost
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
	 * @covers JFacebookGroup::createStatus
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateStatus()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('post')
		->with($group . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createStatus($group, $access_token, $message),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createStatus method - failure.
	 *
	 * @covers JFacebookGroup::createStatus
	 *
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function testCreateStatusFailure()
	{
		$access_token = '235twegsdgsdhtry3tgwgf';
		$group = '134534252457';
		$message = 'This is a message';

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		$returnData = new stdClass;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('post')
		->with($group . '/feed' . '?access_token=' . $access_token, $data)
		->will($this->returnValue($returnData));

		try
		{
			$this->object->createStatus($group, $access_token, $message);
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
	 * @covers JFacebookGroup::deleteStatus
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
	 * @covers JFacebookGroup::deleteStatus
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
}
