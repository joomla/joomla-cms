<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

/**
 * Test class for JHttpTransport classes.
 */
class JHttpTransportTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Options for the JHttpTransport object.
	 *
	 * @var  array
	 */
	protected $options = array(
		'transport.curl'   => array(CURLOPT_SSL_VERIFYPEER => false),
		'transport.socket' => array('X-Joomla-Test: true'),
		'transport.stream' => array('ignore_errors' => true)
	);

	/**
	 * The URL string for the HTTP stub.
	 *
	 * @var  string
	 */
	protected $stubUrl;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		if (!defined('JTEST_HTTP_STUB') && getenv('JTEST_HTTP_STUB') == '')
		{
			$this->markTestSkipped('The JHttpTransport test stub has not been configured');
		}

		$this->options = $this->getMock('\\Joomla\\Registry\\Registry', array('get', 'set'));
		$this->stubUrl = defined('JTEST_HTTP_STUB') ? JTEST_HTTP_STUB : getenv('JTEST_HTTP_STUB');
	}

	/**
	 * Data provider for the request test methods.
	 *
	 * @return  array
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
	 */
	public function testRequestGet($transportClass)
	{
		$transport = new $transportClass(new Registry($this->options));

		$response = $transport->request('get', new JUri($this->stubUrl));

		$body = json_decode($response->body);

		$this->assertEquals(
			200,
			$response->code
		);

		$this->assertEquals(
			'GET',
			$body->method
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
	 */
	public function testBadDomainRequestGet($transportClass)
	{
		$transport = new $transportClass(new Registry($this->options));
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
	 */
	public function testRequestGet404($transportClass)
	{
		$transport = new $transportClass(new Registry($this->options));

		$response = $transport->request('get', new JUri($this->stubUrl . ':80'));

		$this->assertEquals(
			404,
			$response->code
		);
	}

	/**
	 * Tests the request method with a put request
	 *
	 * @param   string  $transportClass  The transport class to test
	 *
	 * @return  void
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestPut($transportClass)
	{
		$transport = new $transportClass(new Registry($this->options));

		$response = $transport->request('put', new JUri($this->stubUrl));

		$body = json_decode($response->body);

		$this->assertEquals(
			200,
			$response->code
		);

		$this->assertEquals(
			'PUT',
			$body->method
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
	 */
	public function testRequestPost($transportClass)
	{
		$transport = new $transportClass(new Registry($this->options));

		$response = $transport->request('post', new JUri($this->stubUrl . '?test=okay'), array('key' => 'value'));

		$body = json_decode($response->body);

		$this->assertEquals(
			200,
			$response->code
		);

		$this->assertEquals(
			'POST',
			$body->method
		);

		$this->assertEquals(
			'value',
			$body->post->key
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
	 */
	public function testRequestPostScalar($transportClass)
	{
		$transport = new $transportClass(new Registry($this->options));

		$response = $transport->request('post', new JUri($this->stubUrl . '?test=okay'), 'key=value');

		$body = json_decode($response->body);

		$this->assertEquals(
			200,
			$response->code
		);

		$this->assertEquals(
			'POST',
			$body->method
		);

		$this->assertEquals(
			'value',
			$body->post->key
		);
	}
}
