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
 * @since       12.3
 */
class JLinkedinCompaniesTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JLinkedinHttp  Mock http object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JLinkedinCompanies  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    JLinkedinOAuth  Authentication object for the Twitter object.
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
	protected $errorString = '{"errorCode":401, "message": "Generic error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$key = "lIio7RcLe5IASG5jpnZrA";
		$secret = "dl3BrWij7LT04NUpy37BRJxGXpWgjNvMrneuQ11EveE";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/linkedin_test.php";

		$this->options = new JRegistry;
		$this->client = $this->getMock('JLinkedinHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JLinkedinCompanies($this->options, $this->client);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->oauth = new JLinkedinOAuth($this->options, $this->client);
		$this->oauth->setToken($key, $secret);
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
	* @since 12.3
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
	 * @return  void
	 *
	 * @dataProvider seedGetCompanies
	 * @since   12.3
	 */
	public function testGetCompanies($id, $name, $domain)
	{
		$fields = '(id,name,ticker,description)';

		if ($id == null && $name == null && $domain == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getCompanies($this->oauth, $id, $name, $domain, $fields);
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
			$this->object->getCompanies($this->oauth, $id, $name, $domain, $fields),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCompanies method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$this->object->getCompanies($this->oauth, $id, $name, $domain, $fields);
	}

	/**
	 * Tests the getUpdates method
	 *
	 * @return  void
	 *
	 * @since   12.3
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
			$this->object->getUpdates($this->oauth, $id, $type, $count, $start),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getUpdates method - failure
	 *
	 * @return  void
	 *
	 * @expectedException DomainException
	 * @since   12.3
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

		$this->object->getUpdates($this->oauth, $id, $type, $count, $start);
	}
}
