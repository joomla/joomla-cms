<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTwitterBlock.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterBlockTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since 12.3
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since 12.3
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  12.3
	 */
	protected $input;

	/**
	 * @var    JTwitterBlock  Object under test.
	 * @since 12.3
	 */
	protected $object;

	/**
	 * @var    JTwitterOauth  Authentication object for the Twitter object.
	 * @since 12.3
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since 12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since 12.3
	 */
	protected $errorString = '{"error":"Generic error"}';

	/**
	 * @var    string  Sample JSON string.
	 * @since 12.3
	 */
	protected $rateLimit = '{"resources": {"blocks": {
			"/blocks/ids": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/blocks/create": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/blocks/destroy": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
			}}}';

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
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->oauth = new JTwitterOAuth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JTwitterBlock($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->options->set('sendheaders', true);
	}

	/**
	 * Tests the getBlocking method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testGetBlocking()
	{
		$stringify_ids = true;
		$cursor = 123;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "blocks"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['stringify_ids'] = $stringify_ids;
		$data['cursor'] = $cursor;

		$path = $this->object->fetchUrl('/blocks/ids.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getBlocking($stringify_ids, $cursor),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getBlocking method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testGetBlockingFailure()
	{
		$stringify_ids = true;
		$cursor = 123;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "blocks"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$data['stringify_ids'] = $stringify_ids;
		$data['cursor'] = $cursor;

		$path = $this->object->fetchUrl('/blocks/ids.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getBlocking($stringify_ids, $cursor);
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
	 * Tests the block method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedUser
	 * @since 12.3
	 */
	public function testBlock($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "blocks"));

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
			$this->object->block($user);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/blocks/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->block($user, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the block method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedUser
	 * @expectedException DomainException
	 * @since 12.3
	 */
	public function testBlockFailure($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "blocks"));

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
			$this->object->block($user);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/blocks/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->block($user, $entities, $skip_status);
	}

	/**
	 * Tests the unblock method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedUser
	 * @since 12.3
	 */
	public function testUnlock($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "blocks"));

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
			$this->object->unblock($user);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/blocks/destroy.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->unblock($user, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the unblock method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedUser
	 * @expectedException DomainException
	 * @since 12.3
	 */
	public function testUnblockFailure($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "blocks"));

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
			$this->object->unblock($user);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/blocks/destroy.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->unblock($user, $entities, $skip_status);
	}
}
