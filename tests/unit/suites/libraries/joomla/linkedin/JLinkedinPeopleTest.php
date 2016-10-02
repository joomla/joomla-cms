<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLinkedinPeople.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedinPeopleTest extends TestCase
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
	 * @var    JLinkedinPeople  Object under test.
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
	 * @var    string  Sample JSON string used to access out of network profiles.
	 * @since  13.1
	 */
	protected $outString = '{"headers": { "_total": 1, "values": [{ "name": "x-li-auth-token",
				"value": "NAME_SEARCH:-Ogn" }] }, "url": "/v1/people/oAFz-3CZyv"}';

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

		$this->object = new JLinkedinPeople($this->options, $this->client, $this->oauth);

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
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 13.1
	*/
	public function seedIdUrl()
	{
		// Member ID or url
		return array(
			array('lcnIwDU0S6', null),
			array(null, 'http://www.linkedin.com/in/dianaprajescu'),
			array(null, null)
			);
	}

	/**
	 * Tests the getProfile method
	 *
	 * @param   string  $id   Member id of the profile you want.
	 * @param   string  $url  The public profile URL.
	 *
	 * @return  void
	 *
	 * @dataProvider seedIdUrl
	 * @since   13.1
	 */
	public function testGetProfile($id, $url)
	{
		$fields = '(id,first-name,last-name)';
		$language = 'en-US';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/';

		if ($url)
		{
			$path .= 'url=' . $this->oauth->safeEncode($url) . ':public';
			$type = 'public';
		}
		else
		{
			$type = 'standard';
		}

		if ($id)
		{
			$path .= 'id=' . $id;
		}
		elseif (!$url)
		{
			$path .= '~';
		}

		$path .= ':' . $fields;
		$header = array('Accept-Language' => $language);

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get', $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getProfile($id, $url, $fields, $type, $language),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getProfile method - failure
	 *
	 * @param   string  $id   Member id of the profile you want.
	 * @param   string  $url  The public profile URL.
	 *
	 * @return  void
	 *
	 * @dataProvider seedIdUrl
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testGetProfileFailure($id, $url)
	{
		$fields = '(id,first-name,last-name)';
		$language = 'en-US';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/';

		if ($url)
		{
			$path .= 'url=' . $this->oauth->safeEncode($url) . ':public';
			$type = 'public';
		}
		else
		{
			$type = 'standard';
		}

		if ($id)
		{
			$path .= 'id=' . $id;
		}
		elseif (!$url)
		{
			$path .= '~';
		}

		$path .= ':' . $fields;
		$header = array('Accept-Language' => $language);

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get', $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getProfile($id, $url, $fields, $type, $language);
	}

	/**
	 * Tests the getConnections method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetConnections()
	{
		$fields = '(id,first-name,last-name)';
		$start = 1;
		$count = 50;
		$modified = 'new';
		$modified_since = '1267401600000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['modified'] = $modified;
		$data['modified-since'] = $modified_since;

		$path = '/v1/people/~/connections';

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
			$this->object->getConnections($fields, $start, $count, $modified, $modified_since),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getConnections method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testGetConnectionsFailure()
	{
		$fields = '(id,first-name,last-name)';
		$start = 1;
		$count = 50;
		$modified = 'new';
		$modified_since = '1267401600000';

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;
		$data['modified'] = $modified;
		$data['modified-since'] = $modified_since;

		$path = '/v1/people/~/connections';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getConnections($fields, $start, $count, $modified, $modified_since);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 13.1
	*/
	public function seedFields()
	{
		// Fields
		return array(
			array('(people:(id,first-name,last-name,api-standard-profile-request))'),
			array('(people:(id,first-name,last-name))')
			);
	}

	/**
	 * Tests the search method
	 *
	 * @param   string  $fields  Request fields beyond the default ones. provide 'api-standard-profile-request' field for out of network profiles.
	 *
	 * @return  void
	 *
	 * @dataProvider seedFields
	 * @since   13.1
	 */
	public function testSearch($fields)
	{
		$keywords = 'Princess';
		$first_name = 'Clair';
		$last_name = 'Standish';
		$company_name = 'Smth';
		$current_company = true;
		$title = 'developer';
		$current_title = true;
		$school_name = 'Shermer High School';
		$current_school = true;
		$country_code = 'us';
		$postal_code = 12345;
		$distance = 500;
		$facets = 'location,industry,network,language,current-company,past-company,school';
		$facet = array('us-84', 47, 'F', 'en', 1006, 1028, 2345);
		$start = 1;
		$count = 50;
		$sort = 'distance';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['first-name'] = $first_name;
		$data['last-name'] = $last_name;
		$data['company-name'] = $company_name;
		$data['current-company'] = $current_company;
		$data['title'] = $title;
		$data['current-title'] = $current_title;
		$data['school-name'] = $school_name;
		$data['current-school'] = $current_school;
		$data['country-code'] = $country_code;
		$data['postal-code'] = $postal_code;
		$data['distance'] = $distance;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'location,' . $facet[0];
		$data['facet'][] = 'industry,' . $facet[1];
		$data['facet'][] = 'network,' . $facet[2];
		$data['facet'][] = 'language,' . $facet[3];
		$data['facet'][] = 'current-company,' . $facet[4];
		$data['facet'][] = 'past-company,' . $facet[5];
		$data['facet'][] = 'school,' . $facet[6];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/people-search';

		$path .= ':' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		if (strpos($fields, 'api-standard-profile-request') === false)
		{
			$this->client->expects($this->once())
				->method('get')
				->with($path)
				->will($this->returnValue($returnData));
		}
		else
		{
			$returnData = new stdClass;
			$returnData->code = 200;
			$returnData->body = $this->outString;

			$this->client->expects($this->at(0))
				->method('get')
				->with($path)
				->will($this->returnValue($returnData));

			$returnData = new stdClass;
			$returnData->code = 200;
			$returnData->body = $this->sampleString;

			$path = '/v1/people/oAFz-3CZyv';
			$path = $this->oauth->toUrl($path, $data);

			$name = 'x-li-auth-token';
			$value = 'NAME_SEARCH:-Ogn';
			$header[$name] = $value;

			$this->client->expects($this->at(1))
				->method('get', $header)
				->with($path)
				->will($this->returnValue($returnData));
		}

		$this->assertThat(
			$this->object->search(
				$fields, $keywords, $first_name, $last_name, $company_name,
				$current_company, $title, $current_title, $school_name, $current_school, $country_code,
				$postal_code, $distance, $facets, $facet, $start, $count, $sort
				),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the search method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException DomainException
	 */
	public function testSearchFailure()
	{
		$fields = '(id,first-name,last-name)';
		$keywords = 'Princess';
		$first_name = 'Clair';
		$last_name = 'Standish';
		$company_name = 'Smth';
		$current_company = true;
		$title = 'developer';
		$current_title = true;
		$school_name = 'Shermer High School';
		$current_school = true;
		$country_code = 'us';
		$postal_code = 12345;
		$distance = 500;
		$facets = 'location,industry,network,language,current-company,past-company,school';
		$facet = array('us-84', 47, 'F', 'en', 1006, 1028, 2345);
		$start = 1;
		$count = 50;
		$sort = 'distance';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['first-name'] = $first_name;
		$data['last-name'] = $last_name;
		$data['company-name'] = $company_name;
		$data['current-company'] = $current_company;
		$data['title'] = $title;
		$data['current-title'] = $current_title;
		$data['school-name'] = $school_name;
		$data['current-school'] = $current_school;
		$data['country-code'] = $country_code;
		$data['postal-code'] = $postal_code;
		$data['distance'] = $distance;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'location,' . $facet[0];
		$data['facet'][] = 'industry,' . $facet[1];
		$data['facet'][] = 'network,' . $facet[2];
		$data['facet'][] = 'language,' . $facet[3];
		$data['facet'][] = 'current-company,' . $facet[4];
		$data['facet'][] = 'past-company,' . $facet[5];
		$data['facet'][] = 'school,' . $facet[6];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/people-search';

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
			$fields, $keywords, $first_name, $last_name, $company_name,
			$current_company, $title, $current_title, $school_name, $current_school, $country_code,
			$postal_code, $distance, $facets, $facet, $start, $count, $sort
			);
	}
}
