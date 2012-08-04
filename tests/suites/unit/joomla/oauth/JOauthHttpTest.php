<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/oauth/http.php';
require_once JPATH_PLATFORM . '/joomla/http/transport/stream.php';

/**
 * Test class for JOauth.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Oauth
 * @since       12.2
 */
class JOauthHttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Oauth object.
	 * @since  12.2
	 */
	protected $options;

	/**
	 * @var    JHttpTransportStream  Mock client object.
	 * @since  12.2
	 */
	protected $transport;

	/**
	 * @var    JOauthHttp  Object under test.
	 * @since  12.2
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->transport = $this->getMock('JHttpTransportStream', array('request'), array($this->options), 'CustomTransport', false);

		$this->object = new JOauthHttp($this->options, $this->transport);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the patch method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testPatch()
	{
		$uri = new JUri('https://example.com/gettest');

		$this->transport->expects($this->once())
			->method('request')
			->with('PATCH', $uri, array())
			->will($this->returnValue('requestResponse'));

		$this->assertThat(
			$this->object->patch('https://example.com/gettest', array()),
			$this->equalTo('requestResponse')
		);
	}
}
