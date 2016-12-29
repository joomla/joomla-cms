<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHttpTransport classes.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Http
 * @since       3.4
 */
class JHttpTransportTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    \Joomla\Registry\Registry  Options for the JHttpTransport object.
	 * @since  3.4
	 */
	protected $options;

	/**
	 * @var    string  The URL string for the HTTP stub.
	 * @since  3.4
	 */
	protected $stubUrl;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setUp()
	{
		if (!defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '')
		{
			$this->markTestSkipped('The JHttpTransport test stub has not been configured');
		}
		else
		{
			parent::setUp();
			$this->options = $this->getMockBuilder('\\Joomla\\Registry\\Registry')->setMethods(array('get', 'set'))->getMock();
			$this->stubUrl = defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
		}
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
		unset($this->options, $this->stubUrl);
		parent::tearDown();
	}

	/**
	 * Data provider for the request test methods.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function transportProvider()
	{
		return array(
			'stream' => array('JHttpTransportStream'),
			'curl'   => array('JHttpTransportCurl'),
			'socket' => array('JHttpTransportSocket')
		);
	}

	/**
	 * Tests the request method with a get request
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @return  void
	 *
	 * @dataProvider  transportProvider
	 * @since         3.4
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
	 * @return  void
	 *
	 * @dataProvider       transportProvider
	 * @expectedException  RuntimeException
	 * @since              3.4
	 */
	public function testBadDomainRequestGet($transportClass)
	{
		$transport = new $transportClass($this->options);
		$transport->request('get', new JUri('http://xommunity.joomla.org'));
	}

	/**
	 * Tests the request method with a get request for non existant url
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @return  void
	 *
	 * @dataProvider  transportProvider
	 * @since         3.4
	 */
	public function testRequestGet404($transportClass)
	{
		$transport = new $transportClass($this->options);
		$transport->request('get', new JUri($this->stubUrl . ':80'));
	}

	/**
	 * Tests the request method with a put request
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @return  void
	 *
	 * @dataProvider  transportProvider
	 * @since         3.4
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
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @return  void
	 *
	 * @dataProvider  transportProvider
	 * @since         3.4
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
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @return  void
	 *
	 * @dataProvider  transportProvider
	 * @since         3.4
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
