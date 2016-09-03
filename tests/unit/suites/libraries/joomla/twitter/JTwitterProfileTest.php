<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTwitterProfile.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterProfileTest extends TestCase
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
	 * @var    JTwitterProfile  Object under test.
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
	protected $rateLimit = '{"resources": {"account": {
			"/account/update_profile": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/account/update_profile_background_image": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/account/update_profile_image": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/account/update_profile_colors": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/account/settings": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
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
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->oauth = new JTwitterOAuth($this->options, $this->client, $this->input);
		$this->oauth->setToken($access_token);

		$this->object = new JTwitterProfile($this->options, $this->client, $this->oauth);

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
	 * Tests the updateProfile method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testUpdateProfile()
	{
		$name = 'testUser';
		$url = 'www.example.com/url';
		$location = 'San Francisco, CA';
		$description = 'Flipped my wig at age 22 and it never grew back. Also: I work at Twitter.';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['name'] = $name;
		$data['url'] = $url;
		$data['location'] = $location;
		$data['description'] = $description;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/account/update_profile.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateProfile($name, $url, $location, $description, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateProfile method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testUpdateProfileFailure()
	{
		$name = 'testUser';
		$url = 'www.example.com/url';
		$location = 'San Francisco, CA';
		$description = 'Flipped my wig at age 22 and it never grew back. Also: I work at Twitter.';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$data['name'] = $name;
		$data['url'] = $url;
		$data['location'] = $location;
		$data['description'] = $description;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/account/update_profile.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->updateProfile($name, $url, $location, $description, $entities, $skip_status);
	}

	/**
	 * Tests the updateProfileBackgroundImage method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testUpdateProfileBackgroundImage()
	{
		$image = 'path/to/source';
		$tile = true;
		$entities = true;
		$skip_status = true;
		$use = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['image'] = "@{$image}";
		$data['tile'] = $tile;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;
		$data['use'] = $use;

		$this->client->expects($this->at(1))
			->method('post')
			->with('/account/update_profile_background_image.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateProfileBackgroundImage($image, $tile, $entities, $skip_status, $use),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateProfileBackgroundImage method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testUpdateProfileBackgroundImageFailure()
	{
		$image = 'path/to/source';
		$tile = true;
		$entities = true;
		$skip_status = true;
		$use = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['image'] = "@{$image}";
		$data['tile'] = $tile;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;
		$data['use'] = $use;

		$this->client->expects($this->at(1))
			->method('post')
			->with('/account/update_profile_background_image.json', $data)
			->will($this->returnValue($returnData));

		$this->object->updateProfileBackgroundImage($image, $tile, $entities, $skip_status, $use);
	}

	/**
	 * Tests the updateProfileImage method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testUpdateProfileImage()
	{
		$image = 'path/to/source';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['image'] = "@{$image}";
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$this->client->expects($this->at(1))
			->method('post')
			->with('/account/update_profile_image.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateProfileImage($image, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateProfileImage method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testUpdateProfileImageFailure()
	{
		$image = 'path/to/source';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['image'] = "@{$image}";
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$this->client->expects($this->at(1))
			->method('post')
			->with('/account/update_profile_image.json', $data)
			->will($this->returnValue($returnData));

		$this->object->updateProfileImage($image, $entities, $skip_status);
	}

	/**
	 * Tests the updateProfileColors method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testUpdateProfileColors()
	{
		$background = 'C0DEED ';
		$link = '0084B4';
		$sidebar_border = '0084B4';
		$sidebar_fill = 'DDEEF6';
		$text = '333333';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['profile_background_color'] = $background;
		$data['profile_link_color'] = $link;
		$data['profile_sidebar_border_color'] = $sidebar_border;
		$data['profile_sidebar_fill_color'] = $sidebar_fill;
		$data['profile_text_color'] = $text;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$this->client->expects($this->at(1))
			->method('post')
			->with('/account/update_profile_colors.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateProfileColors($background, $link, $sidebar_border, $sidebar_fill, $text, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateProfileColors method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testUpdateProfileColorsFailure()
	{
		$background = 'C0DEED ';
		$link = '0084B4';
		$sidebar_border = '0084B4';
		$sidebar_fill = 'DDEEF6';
		$text = '333333';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['profile_background_color'] = $background;
		$data['profile_link_color'] = $link;
		$data['profile_sidebar_border_color'] = $sidebar_border;
		$data['profile_sidebar_fill_color'] = $sidebar_fill;
		$data['profile_text_color'] = $text;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$this->client->expects($this->at(1))
			->method('post')
			->with('/account/update_profile_colors.json', $data)
			->will($this->returnValue($returnData));

		$this->object->updateProfileColors($background, $link, $sidebar_border, $sidebar_fill, $text, $entities, $skip_status);
	}

	/**
	 * Tests the getSettings method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetSettings()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->at(1))
			->method('get')
			->with('/account/settings.json')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSettings($this->oauth),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSettings method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testGetSettingsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "account"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->at(1))
			->method('get')
			->with('/account/settings.json')
			->will($this->returnValue($returnData));

		$this->object->getSettings($this->oauth);
	}

	/**
	 * Tests the updateSettings method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testUpdateSettings()
	{
		$location = 1;
		$sleep_time = true;
		$start_sleep = 10;
		$end_sleep = 14;
		$time_zone = 'Europe/Copenhagen';
		$lang = 'en';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['trend_location_woeid '] = $location;
		$data['sleep_time_enabled'] = $sleep_time;
		$data['start_sleep_time'] = $start_sleep;
		$data['end_sleep_time'] = $end_sleep;
		$data['time_zone'] = $time_zone;
		$data['lang'] = $lang;

		$this->client->expects($this->once())
			->method('post')
			->with('/account/settings.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateSettings($location, $sleep_time, $start_sleep, $end_sleep, $time_zone, $lang),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateSettings method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testUpdateSettingsFailure()
	{
		$location = 1;
		$sleep_time = true;
		$start_sleep = 10;
		$end_sleep = 14;
		$time_zone = 'Europe/Copenhagen';
		$lang = 'en';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['trend_location_woeid '] = $location;
		$data['sleep_time_enabled'] = $sleep_time;
		$data['start_sleep_time'] = $start_sleep;
		$data['end_sleep_time'] = $end_sleep;
		$data['time_zone'] = $time_zone;
		$data['lang'] = $lang;

		$this->client->expects($this->once())
			->method('post')
			->with('/account/settings.json', $data)
			->will($this->returnValue($returnData));

		$this->object->updateSettings($location, $sleep_time, $start_sleep, $end_sleep, $time_zone, $lang);
	}
}
