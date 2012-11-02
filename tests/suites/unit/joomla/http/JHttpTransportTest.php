<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHttpTransport classes.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @since       11.1
 */
class JHttpTransportTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the JHttpTransport object.
	 */
	protected $options;

	/**
	 * @var    string  The URL string for the HTTP stub.
	 */
	protected $stubUrl;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = $this->getMock('JRegistry', array('get', 'set'));

		if (!defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '')
		{
			$this->markTestSkipped('The JHttpTransport test stub has not been configured');
		}
		else
		{
			$this->stubUrl = defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
		}
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
	 * Data provider for the request test methods.
	 *
	 * @return array
	 */
	public function transportProvider()
	{
		return array(
			'stream' => array('JHttpTransportStream'),
			'curl' => array('JHttpTransportCurl'),
			'socket' => array('JHttpTransportSocket')
		);
	}

	/**
	 * Tests the request method with a get request
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @dataProvider  transportProvider
	 *
	 * @return void
	 */
	public function testRequestGet($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('get', new JUri($this->stubUrl));

		$body = json_decode($response->body);

		$this->assertThat(
			$response->code,
			$this->equalTo(200)
		);

		$this->assertThat(
			$body->method,
			$this->equalTo('GET')
		);
	}

	/**
	 * Tests the request method with a get request with a bad domain
	 *
	 * @param   string  $transportClass  The transport class to test
	 * 
	 * @dataProvider      transportProvider
	 * @expectedException RuntimeException
	 *
	 * @return void
	 */
	public function testBadDomainRequestGet($transportClass)
	{
		$transport = new $transportClass($this->options);
		$response = $transport->request('get', new JUri('http://xommunity.joomla.org'));
	}

	/**
	 * Tests the request method with a get request for non existant url
	 *
	 * @param   string  $transportClass  The transport class to test
	 * 
	 * @dataProvider  transportProvider
	 *
	 * @return void
	 */
	public function testRequestGet404($transportClass)
	{
		$transport = new $transportClass($this->options);
		$response = $transport->request('get', new JUri($this->stubUrl . ':80'));
	}

	/**
	 * Tests the request method with a put request
	 *
	 * @param   string  $transportClass  @todo
	 *
	 * @dataProvider  transportProvider
	 *
	 * @return void
	 */
	public function testRequestPut($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('put', new JUri($this->stubUrl));

		$body = json_decode($response->body);

		$this->assertThat(
			$response->code,
			$this->equalTo(200)
		);

		$this->assertThat(
			$body->method,
			$this->equalTo('PUT')
		);
	}

	/**
	 * Tests the request method with a post request and array data
	 *
	 * @param   string  $transportClass  @todo
	 *
	 * @dataProvider  transportProvider
	 *
	 * @return void
	 */
	public function testRequestPost($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('post', new JUri($this->stubUrl . '?test=okay'), array('key' => 'value'));

		$body = json_decode($response->body);

		$this->assertThat(
			$response->code,
			$this->equalTo(200)
		);

		$this->assertThat(
			$body->method,
			$this->equalTo('POST')
		);

		$this->assertThat(
			$body->post->key,
			$this->equalTo('value')
		);
	}

	/**
	 * Tests the request method with a post request and scalar data
	 *
	 * @param   string  $transportClass  @todo
	 *
	 * @dataProvider  transportProvider
	 *
	 * @return void
	 */
	public function testRequestPostScalar($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('post', new JUri($this->stubUrl . '?test=okay'), 'key=value');

		$body = json_decode($response->body);

		$this->assertThat(
			$response->code,
			$this->equalTo(200)
		);

		$this->assertThat(
			$body->method,
			$this->equalTo('POST')
		);

		$this->assertThat(
			$body->post->key,
			$this->equalTo('value')
		);
	}
}
