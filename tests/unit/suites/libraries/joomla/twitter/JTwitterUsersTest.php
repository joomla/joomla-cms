<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTwitterUsers.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterUsersTest extends TestCase
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
	 * @var    JTwitterUsers  Object under test.
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
	protected $rateLimit = '{"resources": {"users": {
			"/users/lookup": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/profile_banner": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/search": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/show": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/contributees": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/contributors": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/suggestions": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/suggestions/:slug": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/users/suggestions/:slug/members": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
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
		$my_url = "http://127.0.0.1/twitter_test.php";

		$access_token = array('key' => 'token_key', 'secret' => 'token_secret');

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->oauth = new JTwitterOAuth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JTwitterUsers($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->options->set('sendheaders', true);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedFriendshipsLookup()
	{
		// User ID and screen name
		return array(
			array(null, '234654235457'),
			array(null, '234654235457,245864573437'),
			array('testUser', null),
			array('testUser', '234654235457'),
			array(null, null)
			);
	}

	/**
	 * Tests the getUsersLookup method
	 *
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string  $id           A comma separated list of user IDs, up to 100 are allowed in a single request.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedFriendshipsLookup
	 */
	public function testGetUsersLookup($screen_name, $id)
	{
		$entities = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if ($id)
		{
			$data['user_id'] = $id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getUsersLookup($screen_name, $id);
		}

		$data['include_entities'] = $entities;

		$this->client->expects($this->at(1))
		->method('post')
		->with('/users/lookup.json', $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUsersLookup($screen_name, $id, $entities),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUsersLookup method - failure
	 *
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string  $id           A comma separated list of user IDs, up to 100 are allowed in a single request.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedFriendshipsLookup
	 * @expectedException  DomainException
	 */
	public function testGetUsersLookupFailure($screen_name, $id)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if ($id)
		{
			$data['user_id'] = $id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getUsersLookup($screen_name, $id);
		}

		$this->client->expects($this->at(1))
		->method('post')
		->with('/users/lookup.json', $data)
		->will($this->returnValue($returnData));

		$this->object->getUsersLookup($screen_name, $id);
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
	 * Tests the getUserProfileBanner method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @dataProvider seedUser
	 * @since 12.3
	 */
	public function testGetUserProfileBanner($user)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getUserProfileBanner($user);
		}

		$path = $this->object->fetchUrl('/users/profile_banner.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserProfileBanner($user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUserProfileBanner method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 * @expectedException  DomainException
	 */
	public function testGetUserProfileBannerFailure($user)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getUserProfileBanner($user);
		}

		$path = $this->object->fetchUrl('/users/profile_banner.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getUserProfileBanner($user);
	}

	/**
	 * Tests the searchUsers method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testSearchUsers()
	{
		$query = 'testUser';
		$page = 1;
		$count = 20;
		$entities = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['q'] = $query;
		$data['page'] = $page;
		$data['count'] = $count;
		$data['include_entities'] = $entities;

		$path = $this->object->fetchUrl('/users/search.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->searchUsers($query, $page, $count, $entities),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the searchUsers method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException  DomainException
	 */
	public function testSearchUsersFailure()
	{
		$query = 'testUser';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['q'] = $query;

		$path = $this->object->fetchUrl('/users/search.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->searchUsers($query);
	}

	/**
	 * Tests the getUser method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 */
	public function testGetUser($user)
	{
		$entities = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getUser($user, $entities);
		}
		$data['include_entities'] = $entities;

		$path = $this->object->fetchUrl('/users/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUser($user, $entities),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUser method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetUserFailure($user)
	{
		$entities = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getUser($user, $entities);
		}
		$data['include_entities'] = $entities;

		$path = $this->object->fetchUrl('/users/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getUser($user, $entities);
	}

	/**
	 * Tests the getContributees method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 */
	public function testGetContributees($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getContributees($user, $entities, $skip_status);
		}
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/users/contributees.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getContributees($user, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getContributees method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetContributeesFailure($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getContributees($user, $entities, $skip_status);
		}
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/users/contributees.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getContributees($user, $entities, $skip_status);
	}

	/**
	 * Tests the getContributors method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 */
	public function testGetContributors($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getContributors($user, $entities, $skip_status);
		}
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/users/contributors.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getContributors($user, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getContributors method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetContributorsFailure($user)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

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
			$this->object->getContributors($user, $entities, $skip_status);
		}
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/users/contributors.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getContributors($user, $entities, $skip_status);
	}

	/**
	 * Tests the getSuggestions method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testGetSuggestions()
	{
		$lang = 'fr';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['lang'] = $lang;

		$path = $this->object->fetchUrl('/users/suggestions.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSuggestions($lang),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSuggestions method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testGetSuggestionsFailure()
	{
		$lang = 'fr';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['lang'] = $lang;

		$path = $this->object->fetchUrl('/users/suggestions.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSuggestions($lang);
	}

	/**
	 * Tests the getSuggestionsSlug method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testGetSuggestionsSlug()
	{
		$slug = 'twitter';
		$lang = 'fr';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['lang'] = $lang;

		$path = $this->object->fetchUrl('/users/suggestions/' . $slug . '.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSuggestionsSlug($slug, $lang),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSuggestionsSlug method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testGetSuggestionsSlugFailure()
	{
		$slug = 'twitter';
		$lang = 'fr';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['lang'] = $lang;

		$path = $this->object->fetchUrl('/users/suggestions/' . $slug . '.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSuggestionsSlug($slug, $lang);
	}

	/**
	 * Tests the getSuggestionsSlugMembers method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testGetSuggestionsSlugMembers()
	{
		$slug = 'twitter';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->object->fetchUrl('/users/suggestions/' . $slug . '/members.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSuggestionsSlugMembers($slug),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSuggestionsSlugMembers method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testGetSuggestionsSlugMembersFailure()
	{
		$slug = 'twitter';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "users"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $this->object->fetchUrl('/users/suggestions/' . $slug . '/members.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSuggestionsSlugMembers($slug);
	}
}
