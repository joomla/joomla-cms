<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTwittersearch.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterSearchTest extends TestCase
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
	 * @var    JTwitterSearch  Object under test.
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
	protected $searchRateLimit = '{"resources": {"search": {
			"/search/tweets": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
			}}}';

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $savedSearchesRateLimit = '{"resources": {"saved_searches": {
			"/saved_searches/list": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/saved_searches/show/:id": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"},
			"/saved_searches/destroy/:id": {"remaining":15, "reset":"Mon Jun 25 17:20:53 +0000 2012"}
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

		$this->object = new JTwitterSearch($this->options, $this->client, $this->oauth);

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
	 * Tests the search method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSearch()
	{
		$query = '@noradio';
		$callback = 'callback';
		$geocode = '37.781157,-122.398720,1mi';
		$lang = 'fr';
		$locale = 'ja';
		$result_type = 'recent';
		$count = 10;
		$until = '2010-03-28';
		$since_id = 12345;
		$max_id = 54321;
		$entities = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->searchRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "search"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		$data['q'] = rawurlencode($query);
		$data['callback'] = $callback;
		$data['geocode'] = $geocode;
		$data['lang'] = $lang;
		$data['locale'] = $locale;
		$data['result_type'] = $result_type;
		$data['count'] = $count;
		$data['until'] = $until;
		$data['since_id'] = $since_id;
		$data['max_id'] = $max_id;
		$data['include_entities'] = $entities;

		$path = $this->object->fetchUrl('/search/tweets.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->search($query, $callback, $geocode, $lang, $locale, $result_type, $count, $until, $since_id, $max_id, $entities),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the search method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testSearchFailure()
	{
		$query = '@noradio';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->searchRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "search"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		$data['q'] = rawurlencode($query);

		$path = $this->object->fetchUrl('/search/tweets.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->search($query);
	}

	/**
	 * Tests the getSavedSearches method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetSavedSearches()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->savedSearchesRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "saved_searches"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->object->fetchUrl('/saved_searches/list.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSavedSearches(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSavedSearches method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testGetSavedSearchesFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->savedSearchesRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "saved_searches"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $this->object->fetchUrl('/saved_searches/list.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSavedSearches();
	}

	/**
	 * Tests the getSavedSearchesById method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetSavedSearchesById()
	{
		$id = 12345;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->savedSearchesRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "saved_searches"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->object->fetchUrl('/saved_searches/show/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSavedSearchesById($id),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSavedSearchesById method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testGetSavedSearchesByIdFailure()
	{
		$id = 12345;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->savedSearchesRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "saved_searches"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $this->object->fetchUrl('/saved_searches/show/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSavedSearchesById($id);
	}

	/**
	 * Tests the createSavedSearch method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreateSavedSearch()
	{
		$query = 'test';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request data
		$data['query'] = $query;

		$path = $this->object->fetchUrl('/saved_searches/create.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createSavedSearch($query),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createSavedSearch method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testCreateSavedSearchFailure()
	{
		$query = 'test';

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request data
		$data['query'] = $query;

		$path = $this->object->fetchUrl('/saved_searches/create.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->createSavedSearch($query);
	}

	/**
	 * Tests the deleteSavedSearch method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDeleteSavedSearch()
	{
		$id = 12345;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->savedSearchesRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "saved_searches"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->object->fetchUrl('/saved_searches/destroy/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteSavedSearch($id),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteSavedSearch method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testDeleteSavedSearchFailure()
	{
		$id = 12345;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->savedSearchesRateLimit;

		$path = $this->object->fetchUrl('/application/rate_limit_status.json', array("resources" => "saved_searches"));

		$this->client->expects($this->at(0))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $this->object->fetchUrl('/saved_searches/destroy/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->deleteSavedSearch($id);
	}
}
