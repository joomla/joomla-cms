<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTwitterLists.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterListsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  12.3
	 */
	protected $input;

	/**
	 * @var    JTwitterLists  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    JTwitterOauth  Authentication object for the Twitter object.
	 * @since  12.3
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"error":"Generic error"}';

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $rateLimit = '{"resources": {"lists": {
			"/lists/list": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/statuses": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/subscribers": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/subscribers/create": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/members/show": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/subscribers/show": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/subscribers/destroy": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/members/create_all": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/members": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/show": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/subscriptions": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/update": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/create": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/lists/destroy": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
			}}}';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 * @since  3.6
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/twitter_test.php";

		$access_token = array('key' => 'token_key', 'secret' => 'token_secret');

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();
		$this->oauth = new JTwitterOAuth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JTwitterLists($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->options->set('sendheaders', true);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->input);
		unset($this->client);
		unset($this->oauth);
		unset($this->object);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedUser()
	{
		// User ID or screen name
		return array(
			array(234654235457),
			array('testUser'),
			array(null)
			);
	}

	/**
	 * Tests the getAllLists method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 */
	public function testGetLists($user)
	{
		$reverse = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getLists($user);
		}

		$data['reverse'] = true;

		$path = $this->object->fetchUrl('/lists/list.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLists($user, $reverse),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getAllLists method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetListsFailure($user)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getLists($user);
		}

		$path = $this->object->fetchUrl('/lists/list.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getLists($user);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedListStatuses()
	{
		// List ID or slug and owner
		return array(
			array(234654235457, null),
			array('test-list', 'testUser'),
			array('test-list', 12345),
			array('test-list', null),
			array(null, null)
			);
	}

	/**
	 * Tests the getListStatuses method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testGetStatuses($list, $owner)
	{
		$since_id = 12345;
		$max_id = 54321;
		$count = 10;
		$entities = true;
		$include_rts = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getStatuses($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getStatuses($list, $owner);
		}

		$data['since_id'] = $since_id;
		$data['max_id'] = $max_id;
		$data['count'] = $count;
		$data['include_entities'] = $entities;
		$data['include_rts'] = $include_rts;

		$path = $this->object->fetchUrl('/lists/statuses.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getStatuses($list, $owner, $since_id, $max_id, $count, $entities, $include_rts),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListStatuses method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testGetStatusesFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getStatuses($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getStatuses($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/statuses.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getStatuses($list, $owner);
	}

	/**
	 * Tests the getListSubscribers method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testGetSubscribers($list, $owner)
	{
		$cursor = 1234;
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getSubscribers($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getSubscribers($list, $owner);
		}

		$data['cursor'] = $cursor;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/subscribers.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSubscribers($list, $owner, $cursor, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListSubscribers method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testGetSubscribersFailure($list, $owner)
	{
		$cursor = 1234;
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getSubscribers($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getSubscribers($list, $owner);
		}

		$data['cursor'] = $cursor;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/subscribers.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSubscribers($list, $owner, $cursor, $entities, $skip_status);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedMembers()
	{
		// List, User ID, screen name and owner.
		return array(
			array(234654235457, null, '234654235457', null),
			array('test-list', null, 'userTest', 'testUser'),
			array('test-list', '234654235457', null, '56165105642'),
			array('test-list', 'testUser', null, null),
			array('test-list', null, null, 'testUser'),
			array('test-list', 'testUser', '234654235457', 'userTest'),
			array(null, null, null, null)
			);
	}

	/**
	 * Tests the deleteListMembers method
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedMembers
	 */
	public function testDeleteMembers($list, $user_id, $screen_name, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($user_id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
		}

		$path = $this->object->fetchUrl('/lists/members/destroy_all.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteMembers($list, $user_id, $screen_name, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteListMembers method - failure
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedMembers
	 * @expectedException DomainException
	 */
	public function testDeleteMembersFailure($list, $user_id, $screen_name, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($user_id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
		}

		$path = $this->object->fetchUrl('/lists/members/destroy_all.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->deleteMembers($list, $user_id, $screen_name, $owner);
	}

	/**
	 * Tests the subscribe method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testSubscribe($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->subscribe($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->subscribe($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/subscribers/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->subscribe($list, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the subscribe method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testSubscribeFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->subscribe($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->subscribe($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/subscribers/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->subscribe($list, $owner);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedListUserOwner()
	{
		// List, User and Owner.
		return array(
			array(234654235457, '234654235457', null),
			array('test-list', 'userTest', 'testUser'),
			array('test-list', '234654235457', '56165105642'),
			array('test-list', 'testUser', null),
			array('test-list', null, 'testUser'),
			array(null, null, null)
			);
	}

	/**
	 * Tests the isListMember method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListUserOwner
	 */
	public function testIsMember($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isMember($list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isMember($list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isMember($list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/members/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isMember($list, $user, $owner, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isListMember method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListUserOwner
	 * @expectedException DomainException
	 */
	public function testIsMemberFailure($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isMember($list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isMember($list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isMember($list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/members/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->isMember($list, $user, $owner, $entities, $skip_status);
	}

	/**
	 * Tests the isListSubscriber method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListUserOwner
	 */
	public function testIsSubscriber($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isSubscriber($list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isSubscriber($list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isSubscriber($list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/subscribers/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isSubscriber($list, $user, $owner, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isListSubscriber method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListUserOwner
	 * @expectedException DomainException
	 */
	public function testIsSubscriberFailure($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isSubscriber($list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isSubscriber($list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isSubscriber($list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/subscribers/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->isSubscriber($list, $user, $owner, $entities, $skip_status);
	}

	/**
	 * Tests the unsubscribe method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testUnsubscribe($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->unsubscribe($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->unsubscribe($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/subscribers/destroy.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->unsubscribe($list, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the unsubscribe method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testUnsubscribeFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->unsubscribe($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->unsubscribe($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/subscribers/destroy.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->unsubscribe($list, $owner);
	}

	/**
	 * Tests the addListMembers method
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedMembers
	 */
	public function testAddMembers($list, $user_id, $screen_name, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->addMembers($list, $user_id, $screen_name, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addMembers($list, $user_id, $screen_name, $owner);
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($user_id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addMembers($list, $user_id, $screen_name, $owner);
		}

		$path = $this->object->fetchUrl('/lists/members/create_all.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->addMembers($list, $user_id, $screen_name, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the addListMembers method - failure
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedMembers
	 * @expectedException DomainException
	 */
	public function testAddMembersFailure($list, $user_id, $screen_name, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->addMembers($list, $user_id, $screen_name, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addMembers($list, $user_id, $screen_name, $owner);
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($user_id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addMembers($list, $user_id, $screen_name, $owner);
		}

		$path = $this->object->fetchUrl('/lists/members/create_all.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->addMembers($list, $user_id, $screen_name, $owner);
	}

	/**
	 * Tests the getListMembers method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testGetMembers($list, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getMembers($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getMembers($list, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/members.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMembers($list, $owner, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListMembers method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testGetMembersFailure($list, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getMembers($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getMembers($list, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/lists/members.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getMembers($list, $owner, $entities, $skip_status);
	}

	/**
	 * Tests the getListById method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testGetListById($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getListById($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListById($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListById($list, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListById method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testGetListByIdFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getListById($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListById($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getListById($list, $owner);
	}

	/**
	 * Tests the getSubscriptions method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 */
	public function testGetSubscriptions($user)
	{
		$count = 10;
		$cursor = 1234;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getSubscriptions($user);
		}

		$data['count'] = $count;
		$data['cursor'] = $cursor;

		$path = $this->object->fetchUrl('/lists/subscriptions.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSubscriptions($user, $count, $cursor),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSubscriptions method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetSubscriptionsFailure($user)
	{
		$count = 10;
		$cursor = 1234;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getSubscriptions($user);
		}

		$data['count'] = $count;
		$data['cursor'] = $cursor;

		$path = $this->object->fetchUrl('/lists/subscriptions.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSubscriptions($user, $count, $cursor);
	}

	/**
	 * Tests the updateList method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testUpdate($list, $owner)
	{
		$name = 'test list';
		$mode = 'private';
		$description = 'this is a description';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->update($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->update($list, $owner);
		}

		$data['name'] = $name;
		$data['mode'] = $mode;
		$data['description'] = $description;

		$path = $this->object->fetchUrl('/lists/update.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->update($list, $owner, $name, $mode, $description),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateList method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testUpdateFailure($list, $owner)
	{
		$name = 'test list';
		$mode = 'private';
		$description = 'this is a description';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->update($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->update($list, $owner);
		}

		$data['name'] = $name;
		$data['mode'] = $mode;
		$data['description'] = $description;

		$path = $this->object->fetchUrl('/lists/update.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->update($list, $owner, $name, $mode, $description);
	}

	/**
	 * Tests the createList method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testCreate()
	{
		$name = 'test list';
		$mode = 'private';
		$description = 'this is a description';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['name'] = $name;
		$data['mode'] = $mode;
		$data['description'] = $description;

		$path = $this->object->fetchUrl('/lists/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create($name, $mode, $description),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createList method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testCreateFailure()
	{
		$name = 'test list';
		$mode = 'private';
		$description = 'this is a description';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$data['name'] = $name;
		$data['mode'] = $mode;
		$data['description'] = $description;

		$path = $this->object->fetchUrl('/lists/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->create($name, $mode, $description);
	}

	/**
	 * Tests the deleteList method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 */
	public function testDelete($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->delete($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->delete($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/destroy.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->delete($list, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteList method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testDeleteFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "lists"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->delete($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->delete($list, $owner);
		}

		$path = $this->object->fetchUrl('/lists/destroy.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->delete($list, $owner);
	}
}
