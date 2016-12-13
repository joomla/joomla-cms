<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
* Test class for JTwitterStatuses.
*
* @package     Joomla.UnitTest
* @subpackage  Twitter
*
* @since       12.3
*/
class JTwitterStatusesTest extends TestCase
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
	 * @var    JTwitterStatuses  Object under test.
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
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $rateLimit = '{"resources": {"statuses": {
			"/statuses/show/:id": {"remaining":150, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/statuses/user_timeline": {"remaining":150, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/statuses/mentions_timeline": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/statuses/retweets_of_me": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/statuses/retweeters/ids": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/statuses/retweets/:id": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/statuses/oembed": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
			}}}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"error":"Generic error"}';

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

		$this->object = new JTwitterStatuses($this->options, $this->client, $this->oauth);

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
	 * Tests the getTweetById method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetTweetById()
	{
		$id = 12324354;
		$trim_user = true;
		$entities = true;
		$my_retweet = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data = array();
		$data['trim_user'] = $trim_user;
		$data['include_entities'] = $entities;
		$data['include_my_retweet'] = $my_retweet;

		$path = $this->object->fetchUrl('/statuses/show/' . $id . '.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getTweetById($id, $trim_user, $entities, $my_retweet),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getTweetById method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetTweetByIdFailure()
	{
		$id = 12324354;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $this->object->fetchUrl('/statuses/show/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getTweetById($id);
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
	 * Tests the getUserTimeline method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedUser
	 *
	 * @since   12.3
	 */
	public function testGetUserTimeline($user)
	{
		$count = 10;
		$include_rts = true;
		$contributor = true;
		$no_replies = true;
		$since_id = 10;
		$max_id = 10;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

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
			$this->object->getUserTimeline($user, $count, $include_rts, $no_replies, $since_id, $max_id, $trim_user, $contributor);
		}

		$data['count'] = $count;
		$data['include_rts'] = $include_rts;
		$data['exclude_replies'] = $no_replies;
		$data['since_id'] = $since_id;
		$data['max_id'] = $max_id;
		$data['trim_user'] = $trim_user;
		$data['contributor_details'] = $contributor;

		$path = $this->object->fetchUrl('/statuses/user_timeline.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserTimeline($user, $count, $include_rts, $no_replies, $since_id, $max_id, $trim_user, $contributor),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUserTimeline method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @dataProvider  seedUser
	 * @expectedException  DomainException
	 */
	public function testGetUserTimelineFailure($user)
	{
		$count = 10;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

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
			$this->object->getUserTimeline($user, $count);
		}

		$data['count'] = $count;

		$path = $this->object->fetchUrl('/statuses/user_timeline.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getUserTimeline($user, $count);
	}

	/**
	 * Tests the tweet method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testTweet()
	{
		$status = 'This is a status';
		$in_reply_to_status_id = 1336421235;
		$lat = 42.53;
		$long = 45.21;
		$place_id = '23455ER235V';
		$display_coordinates = true;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data = array();
		$data['status'] = utf8_encode($status);
		$data['in_reply_to_status_id'] = $in_reply_to_status_id;
		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['place_id'] = $place_id;
		$data['display_coordinates'] = $display_coordinates;
		$data['trim_user'] = $trim_user;

		$this->client->expects($this->once())
			->method('post')
			->with('/statuses/update.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
		$this->object->tweet($status, $in_reply_to_status_id, $lat, $long, $place_id, $display_coordinates, $trim_user),
		$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the tweet method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testTweetFailure()
	{
		$status = 'This is a status';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data = array();
		$data['status'] = utf8_encode($status);

		$this->client->expects($this->once())
			->method('post')
			->with('/statuses/update.json', $data)
			->will($this->returnValue($returnData));

		$this->object->tweet($status);
	}

	/**
	 * Tests the getMentions method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetMentions()
	{
		$count = 10;
		$include_rts = true;
		$entities = true;
		$since_id = 10;
		$max_id = 10;
		$trim_user = true;
		$contributor = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data = array();
		$data['count'] = $count;
		$data['include_rts'] = $include_rts;
		$data['include_entities'] = $entities;
		$data['since_id'] = $since_id;
		$data['max_id'] = $max_id;
		$data['trim_user'] = $trim_user;
		$data['contributor_details'] = $contributor;

		$path = $this->object->fetchUrl('/statuses/mentions_timeline.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getMentions($count, $include_rts, $entities, $since_id, $max_id, $trim_user, $contributor),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getMentions method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetMentionsFailure()
	{
		$count = 10;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data = array();
		$data['count'] = $count;

		$path = $this->object->fetchUrl('/statuses/mentions_timeline.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getMentions($count);
	}

	/**
	 * Tests the getRetweetsOfMe method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetRetweetsOfMe()
	{
		$since_id = 10;
		$count = 10;
		$entities = true;
		$user_entities = true;
		$max_id = 10;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['count'] = $count;
		$data['since_id'] = $since_id;
		$data['max_id'] = $max_id;
		$data['trim_user'] = $trim_user;
		$data['include_entities'] = $entities;
		$data['include_user_entities'] = $user_entities;

		$path = $this->object->fetchUrl('/statuses/retweets_of_me.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getRetweetsOfMe($count, $since_id, $entities, $user_entities, $max_id, $trim_user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getRetweetsOfMe method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetRetweetsOfMeFailure()
	{
		$count = 10;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['count'] = $count;

		$path = $this->object->fetchUrl('/statuses/retweets_of_me.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getRetweetsOfMe($count);
	}

	/**
	 * Tests the getRetweetedBy method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetRetweeters()
	{
		$id = 217781292748652545;
		$count = 5;
		$cursor = 1234;
		$stringify_ids = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['id'] = $id;
		$data['count'] = $count;
		$data['cursor'] = $cursor;
		$data['stringify_ids'] = $stringify_ids;

		$path = $this->object->fetchUrl('/statuses/retweeters/ids.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getRetweeters($id, $count, $cursor, $stringify_ids),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getRetweetedBy method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetRetweetersFailure()
	{
		$id = 217781292748652545;
		$count = 5;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['id'] = $id;
		$data['count'] = $count;

		$path = $this->object->fetchUrl('/statuses/retweeters/ids.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getRetweeters($id, $count);
	}

	/**
	 * Tests the getRetweets method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetRetweetsById()
	{
		$id = 217781292748652545;
		$count = 5;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['count'] = $count;
		$data['trim_user'] = $trim_user;

		$path = $this->object->fetchUrl('/statuses/retweets/' . $id . '.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getRetweetsById($id, $count, $trim_user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getRetweets method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetRetweetsByIdFailure()
	{
		$id = 217781292748652545;
		$count = 5;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['count'] = $count;

		$path = $this->object->fetchUrl('/statuses/retweets/' . $id . '.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getRetweetsById($id, $count);
	}

	/**
	 * Tests the deleteTweet method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDeleteTweet()
	{
		$id = 1234329764382109394;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data = array();
		$data['trim_user'] = $trim_user;

		$this->client->expects($this->once())
			->method('post')
			->with('/statuses/destroy/' . $id . '.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
		$this->object->deleteTweet($id, $trim_user),
		$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteTweet method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testDeleteTweetFailure()
	{
		$id = 1234329764389394;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data = array();
		$data['trim_user'] = $trim_user;

		$this->client->expects($this->once())
			->method('post')
			->with('/statuses/destroy/' . $id . '.json', $data)
			->will($this->returnValue($returnData));

		$this->object->deleteTweet($id, $trim_user);
	}

	/**
	 * Tests the retweet method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRetweet()
	{
		$id = 217781292748652545;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['trim_user'] = $trim_user;

		$path = $this->object->fetchUrl('/statuses/retweet/' . $id . '.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->retweet($id, $trim_user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the retweets method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testRetweetFailure()
	{
		$id = 217781292748652545;
		$trim_user = true;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['trim_user'] = $trim_user;

		$path = $this->object->fetchUrl('/statuses/retweet/' . $id . '.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->retweet($id, $trim_user);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedTweetWithMedia()
	{
		// User ID or screen name
		return array(
			array(array("x-mediaratelimit-remaining" => 10)),
			array(array("x-mediaratelimit-remaining" => 0, "x-mediaratelimit-reset" => 1243245654))
			);
	}

	/**
	 * Tests the tweetWithMedia method
	 *
	 * @param   string  $header  The JSON encoded header.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @dataProvider seedTweetWithMedia
	 */
	public function testTweetWithMedia($header)
	{
		$status = 'This is a status';
		$media = 'path/to/source';
		$in_reply_to_status_id = 1336421235;
		$lat = 42.53;
		$long = 45.21;
		$place_id = '23455ER235V';
		$display_coordinates = true;
		$sensitive = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;
		$returnData->headers = $header;

		// Set POST request parameters.
		$data = array();
		$data['media[]'] = "@{$media}";
		$data['status'] = utf8_encode($status);
		$data['in_reply_to_status_id'] = $in_reply_to_status_id;
		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['place_id'] = $place_id;
		$data['display_coordinates'] = $display_coordinates;
		$data['possibly_sensitive'] = $sensitive;

		$this->client->expects($this->once())
			->method('post')
			->with('/statuses/update_with_media.json', $data)
			->will($this->returnValue($returnData));

		$headers_array = $returnData->headers;

		if ($headers_array['x-mediaratelimit-remaining'] == 0)
		{
			$this->setExpectedException('RuntimeException');
		}

		$this->assertThat(
			$this->object->tweetWithMedia($status, $media, $in_reply_to_status_id, $lat, $long, $place_id, $display_coordinates, $sensitive),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the tweetWithMedia method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testTweetWithMediaFailure()
	{
		$status = 'This is a status';
		$media = 'path/to/source';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data = array();
		$data['media[]'] = "@{$media}";
		$data['status'] = utf8_encode($status);

		$this->client->expects($this->once())
			->method('post')
			->with('/statuses/update_with_media.json', $data)
			->will($this->returnValue($returnData));

		$this->object->tweetWithMedia($status, $media);
	}

	/**
	 * Tests the getOembed method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetOembed()
	{
		$id = 217781292748652545;
		$maxwidth = 300;
		$hide_media = true;
		$hide_thread = true;
		$omit_script = true;
		$align = 'center';
		$related = 'twitter';
		$lang = 'fr';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['id'] = $id;
		$data['maxwidth'] = $maxwidth;
		$data['hide_media'] = $hide_media;
		$data['hide_thread'] = $hide_thread;
		$data['omit_script'] = $omit_script;
		$data['align'] = $align;
		$data['related'] = $related;
		$data['lang'] = $lang;

		$path = $this->object->fetchUrl('/statuses/oembed.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getOembed($id, null, $maxwidth, $hide_media, $hide_thread, $omit_script, $align, $related, $lang),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedGetoembed()
	{
		// URL
		return array(
			array('https://twitter.com/twitter/status/99530515043983360'),
			array(null)
			);
	}

	/**
	 * Tests the getOembed method - failure
	 *
	 * @param   mixed  $url  The URL string or null.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @dataProvider seedGetOembed
	 */
	public function testGetOembedFailure($url)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "statuses"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		if ($url)
		{
			// Set request parameters.
			$data['url'] = rawurlencode($url);
			$this->setExpectedException('DomainException');

			$path = $this->object->fetchUrl('/statuses/oembed.json', $data);

			$this->client->expects($this->at(1))
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

			$this->object->getOembed(null, $url);
		}
		else
		{
			$data = array();
			$this->setExpectedException('RuntimeException');

			$this->object->getOembed(null, null);
		}
	}
}
