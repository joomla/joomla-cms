<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/linkedin/companies.php';

/**
 * Test class for JLinkedinCompanies.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedinCompaniesTest extends TestCase
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
	 * @var    JLinkedinCompanies  Object under test.
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
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

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

		$this->object = new JLinkedinCompanies($this->options, $this->client, $this->oauth);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 13.1
	*/
	public function seedGetCompanies()
	{
		// Company id, company name, email-domain
		return array(
			array(123345, null, null),
			array(12345, 'example', null),
			array(null, 'example', null),
			array(null, null, 'example.com'),
			array(null, null, null)
			);
	}

	/**
	 * Tests the getCompanies method
	 *
	 * @param   integer  $id      The unique internal numeric company identifier.
	 * @param   string   $name    The unique string identifier for a company.
	 * @param   string   $domain  Company email domains.
	 *
	 * @return  void
	 *
	 * @dataProvider seedGetCompanies
	 * @since   13.1
	 */
	public function testGetCompanies($id, $name, $domain)
	{
		$fields = '(id,name,ticker,description)';

		if ($id == null && $name == null && $domain == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getCompanies($id, $name, $domain, $fields);
		}

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/companies';

		if ($id && $name)
		{
			$path .= '::(' . $id . ',universal-name=' . $name . ')';
		}
		elseif ($id)
		{
			$path .= '/' . $id;
		}
		elseif ($name)
		{
			$path .= '/universal-name=' . $name;
		}

		if ($domain)
		{
			$data['email-domain'] = $domain;
		}

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
			$this->object->getCompanies($id, $name, $domain, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCompanies method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetCompaniesFailure()
	{
		$id = 12345;
		$name = 'example';
		$domain = 'example.com';
		$fields = '(id,name,ticker,description)';

		// Set request parameters.
		$data['format'] = 'json';
		$data['email-domain'] = $domain;

		$path = '/v1/companies::(' . $id . ',universal-name=' . $name . '):' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getCompanies($id, $name, $domain, $fields);
	}

	/**
	 * Tests the getUpdates method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetUpdates()
	{
		$id = 12345;
		$type = 'new-hire';
		$count = 10;
		$start = 1;

		// Set request parameters.
		$data['format'] = 'json';
		$data['event-type'] = $type;
		$data['count'] = $count;
		$data['start'] = $start;

		$path = '/v1/companies/' . $id . '/updates';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUpdates($id, $type, $count, $start),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUpdates method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetUpdatesFailure()
	{
		$id = 12345;
		$type = 'new-hire';
		$count = 10;
		$start = 1;

		// Set request parameters.
		$data['format'] = 'json';
		$data['event-type'] = $type;
		$data['count'] = $count;
		$data['start'] = $start;

		$path = '/v1/companies/' . $id . '/updates';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getUpdates($id, $type, $count, $start);
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
		$keywords = 'linkedin';
		$hq = true;
		$facets = 'location,industry,network,company-size,num-followers-range,fortune';
		$facet = array('us-84', 47, 'F', 'B', 100, 100);
		$start = 1;
		$count = 50;
		$sort = 'relevance';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['hq-only'] = $hq;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'location,' . $facet[0];
		$data['facet'][] = 'industry,' . $facet[1];
		$data['facet'][] = 'network,' . $facet[2];
		$data['facet'][] = 'company-size,' . $facet[3];
		$data['facet'][] = 'num-followers-range,' . $facet[4];
		$data['facet'][] = 'fortune,' . $facet[5];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/company-search';

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
			$this->object->search($fields, $keywords, $hq, $facets, $facet, $start, $count, $sort),
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
		$keywords = 'linkedin';
		$hq = true;
		$facets = 'location,industry,network,company-size,num-followers-range,fortune';
		$facet = array('us-84', 47, 'F', 'B', 100, 100);
		$start = 1;
		$count = 50;
		$sort = 'relevance';

		// Set request parameters.
		$data['format'] = 'json';
		$data['keywords'] = $keywords;
		$data['hq-only'] = $hq;
		$data['facets'] = $facets;
		$data['facet'] = array();
		$data['facet'][] = 'location,' . $facet[0];
		$data['facet'][] = 'industry,' . $facet[1];
		$data['facet'][] = 'network,' . $facet[2];
		$data['facet'][] = 'company-size,' . $facet[3];
		$data['facet'][] = 'num-followers-range,' . $facet[4];
		$data['facet'][] = 'fortune,' . $facet[5];

		$data['start'] = $start;
		$data['count'] = $count;
		$data['sort'] = $sort;

		$path = '/v1/company-search:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->search($fields, $keywords, $hq, $facets, $facet, $start, $count, $sort);
	}

	/**
	 * Tests the getFollowed method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFollowed()
	{
		$fields = '(id,name,email-domains)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/~/following/companies:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getFollowed($fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFollowed method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetFollowedFailure()
	{
		$fields = '(id,name,email-domains)';

		// Set request parameters.
		$data['format'] = 'json';

		$path = '/v1/people/~/following/companies:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getFollowed($fields);
	}

	/**
	 * Tests the follow method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testFollow()
	{
		$id = '12345';

		$path = '/v1/people/~/following/companies';

		$xml = '<company><id>' . $id . '</id></company>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->follow($id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the follow method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testFollowFailure()
	{
		$id = '12345';

		$path = '/v1/people/~/following/companies';

		$xml = '<company><id>' . $id . '</id></company>';

		$header['Content-Type'] = 'text/xml';

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post', $xml, $header)
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->follow($id);
	}

	/**
	 * Tests the unfollow method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUnfollow()
	{
		$id = '12345';

		$path = '/v1/people/~/following/companies/id=' . $id;

		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->unfollow($id),
			$this->equalTo($returnData)
		);
	}

	/**
	 * Tests the unfollow method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testUnfollowFailure()
	{
		$id = '12345';

		$path = '/v1/people/~/following/companies/id=' . $id;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->unfollow($id);
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
		$fields = '(id,name,email-domains)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/people/~/suggestions/to-follow/companies:' . $fields;

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
		$fields = '(id,name,email-domains)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/people/~/suggestions/to-follow/companies:' . $fields;

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
	 * Tests the getProducts method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetProducts()
	{
		$id = 12345;
		$fields = '(id,name,type,creation-timestamp)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/companies/' . $id . '/products:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getProducts($id, $fields, $start, $count),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getProducts method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   13.1
	 */
	public function testGetProductsFailure()
	{
		$id = 12345;
		$fields = '(id,name,type,creation-timestamp)';
		$start = 1;
		$count = 10;

		// Set request parameters.
		$data['format'] = 'json';
		$data['start'] = $start;
		$data['count'] = $count;

		$path = '/v1/companies/' . $id . '/products:' . $fields;

		$returnData = new stdClass;
		$returnData->code = 401;
		$returnData->body = $this->errorString;

		$path = $this->oauth->toUrl($path, $data);

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->object->getProducts($id, $fields, $start, $count);
	}
}
