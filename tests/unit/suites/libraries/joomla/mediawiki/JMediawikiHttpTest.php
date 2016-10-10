<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JMediawikiHttp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiHttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Mediawiki object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JMediawikiHttp  Mock client object.
	 * @since  12.3
	 */
	protected $transport;

	/**
	 * @var    JMediawikiHttp  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    string  Sample xml string.
	 * @since  12.3
	 */
	protected $sampleString = '<a><b></b><c></c></a>';

	/**
	 * @var    string  Sample xml error message.
	 * @since  12.3
	 */
	protected $errorString = '<message>Generic Error</message>';

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
		$this->options = new JRegistry;
		$this->transport = $this->getMock('JHttpTransportStream', array('request'), array($this->options), 'CustomTransport', false);

		$this->object = new JMediawikiHttp($this->options, $this->transport);
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
		unset($this->options);
		unset($this->transport);
		unset($this->object);
	}

	/**
	 * Tests the get method
	 *
	 * @return void
	 */
	public function testGet()
	{
		$uri = new JUri('https://example.com/gettest');

		$this->transport->expects($this->once())
			->method('request')
			->with('GET', $uri)
			->will($this->returnValue('requestResponse'));

		$this->assertThat(
			$this->object->get('https://example.com/gettest'),
			$this->equalTo('requestResponse')
		);
	}

	/**
	 * Tests the post method
	 *
	 * @return void
	 */
	public function testPost()
	{
		$uri = new JUri('https://example.com/gettest');

		$this->transport->expects($this->once())
			->method('request')
			->with('POST', $uri, array())
			->will($this->returnValue('requestResponse'));

		$this->assertThat(
			$this->object->post('https://example.com/gettest', array()),
			$this->equalTo('requestResponse')
		);
	}
}
