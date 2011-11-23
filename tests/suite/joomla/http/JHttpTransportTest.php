<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/http/http.php';
require_once JPATH_PLATFORM.'/joomla/http/transport/stream.php';
require_once JPATH_PLATFORM.'/joomla/http/transport/curl.php';
require_once JPATH_PLATFORM.'/joomla/http/transport/socket.php';

/**
 * Test class for JHttpTransport classes.
 */
class JHttpTransportTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the JHttpTransport object.
	 */
	protected $options;

	/**
	 * @var    JTestConfig  Test config object.
	 */
	protected $config;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->options = $this->getMock('JRegistry', array('get', 'set'));
		$this->config = new JTestConfig;

		if (!isset($this->config->jhttp_stub) || empty($this->config->jhttp_stub))
		{
			$this->markTestSkipped(
				'The JHttpTransport test stub has not been configured'
			);
		}
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * Data provider for the request test methods.
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
	 * @dataProvider  transportProvider
	 */
	public function testRequestGet($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('get', new JUri($this->config->jhttp_stub));

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
	 * Tests the request method with a put request
	 *
	 * @dataProvider  transportProvider
	 */
	public function testRequestPut($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('put', new JUri($this->config->jhttp_stub));

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
	 * @dataProvider  transportProvider
	 */
	public function testRequestPost($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('post', new JUri($this->config->jhttp_stub . '?test=okay'), array('key' => 'value'));

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
	 * @dataProvider  transportProvider
	 */
	public function testRequestPostScalar($transportClass)
	{
		$transport = new $transportClass($this->options);

		$response = $transport->request('post', new JUri($this->config->jhttp_stub . '?test=okay'), 'key=value');

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
