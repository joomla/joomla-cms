<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLinkedinJobs.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedinJobsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  13.1
	 */
	protected $input;

	/**
	 * @var    JLinkedinJobs  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    JLinkedinOAuth  Authentication object for the Twitter object.
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
	protected $errorString = '{"errorCode":401, "message": "Generic error"}';

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
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/linkedin_test.php";

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->oauth = new JLinkedinOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken(array('key' => $key, 'secret' => $secret));

		$this->object = new JLinkedinJobs($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
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
		parent::tearDown();
	}

	/**
	 * Tests the getJob method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetJob()
	{
		$id = 12345;
		$fields = '(id,company,posting-date)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/jobs/' . $id . ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getJob($id, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getJob method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetJobFailure()
	{
		$id = 12345;
		$fields = '(id,company,posting-date)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/jobs/' . $id . ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getJob($id, $fields);
	}

	/**
	 * Tests the getBookmarked method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetBookmarked()
	{
		$fields = '(id,position)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/~/job-bookmarks:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getBookmarked($fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getBookmarked method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetBookmarkedFailure()
	{
		$fields = '(id,position)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/~/job-bookmarks:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getBookmarked($fields);
	}

	/**
	 * Tests the bookmark method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testBookmark()
	{
		$id = '12345';

		$path = '/v1/people/~/job-bookmarks';

		$xml = '<job-bookmark><job><id>' . $id . '</id></job></job-bookmark>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->bookmark($id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the bookmark method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testBookmarkFailure()
	{
		$id = '12345';

		$path = '/v1/people/~/job-bookmarks';

		$xml = '<job-bookmark><job><id>' . $id . '</id></job></job-bookmark>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->bookmark($id);
	}

	/**
	 * Tests the deleteBookmark method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteBookmark()
	{
		$id = '12345';

		$path = '/v1/people/~/job-bookmarks/' . $id;

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteBookmark($id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the deleteBookmark method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testDeleteBookmarkFailure()
	{
		$id = '12345';

		$path = '/v1/people/~/job-bookmarks/' . $id;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->deleteBookmark($id);
	}

	/**
	 * Tests the getSuggested method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetSuggested()
	{
		$fields = '(jobs)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/people/~/suggestions/job-suggestions:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSuggested($fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSuggested method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetSuggestedFailure()
	{
		$fields = '(jobs)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/people/~/suggestions/job-suggestions:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getSuggested($fields, $start, $count);
	}

	/**
	 * Tests the search method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testSearch()
	{
		$fields = '(facets)';
		$keywords = 'quality, internet';
		$company_name = 'Google';
		$job_title = 'Software Engineer';
		$country_code = 'us';
		$postal_code = 12345;
		$distance = 500;
		$facets = 'company,date-posted,location,job-function,industry,salary';
		$facet = array('Google', 1232435, 'us:84', 'developer', 6, 1000);
		$start = 1;
		$count = 50;
		$sort = 'R';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['company-name'] = $company_name;
		$data['job-title'] = $job_title;
		$data['country-code'] = $country_code;
		$data['postal-code'] = $postal_code;
		$data['distance'] = $distance;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'company,' . $this->oauth->safeEncode($facet[0]);
		$data['facet'][] = 'date-posted,' . $facet[1];
		$data['facet'][] = 'location,' . $facet[2];
		$data['facet'][] = 'job-function,' . $this->oauth->safeEncode($facet[3]);
		$data['facet'][] = 'industry,' . $facet[4];
		$data['facet'][] = 'salary,' . $facet[5];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/job-search';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->search(
				$fields, $keywords, $company_name, $job_title, $country_code, $postal_code, $distance,
				$facets, $facet, $start, $count, $sort
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the search method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testSearchFailure()
	{
		$fields = '(facets)';
		$keywords = 'quality, internet';
		$company_name = 'Google';
		$job_title = 'Software Engineer';
		$country_code = 'us';
		$postal_code = 12345;
		$distance = 500;
		$facets = 'company,date-posted,location,job-function,industry,salary';
		$facet = array('Google', 1232435, 'us:84', 'developer', 6, 1000);
		$start = 1;
		$count = 50;
		$sort = 'R';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['company-name'] = $company_name;
		$data['job-title'] = $job_title;
		$data['country-code'] = $country_code;
		$data['postal-code'] = $postal_code;
		$data['distance'] = $distance;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'company,' . $this->oauth->safeEncode($facet[0]);
		$data['facet'][] = 'date-posted,' . $facet[1];
		$data['facet'][] = 'location,' . $facet[2];
		$data['facet'][] = 'job-function,' . $this->oauth->safeEncode($facet[3]);
		$data['facet'][] = 'industry,' . $facet[4];
		$data['facet'][] = 'salary,' . $facet[5];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/job-search';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->search(
			$fields, $keywords, $company_name, $job_title, $country_code, $postal_code, $distance,
			$facets, $facet, $start, $count, $sort
			);
	}
}
